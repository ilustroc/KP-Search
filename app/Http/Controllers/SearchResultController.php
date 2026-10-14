<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Audit\AuditRecorder;
use App\Services\Search\SearchContextResolver;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchResultController extends Controller
{
    public function show(
        Request $request,
        string $token,
        SearchContextResolver $searchContextResolver,
        AuditRecorder $auditRecorder,
    ): View {
        /** @var User $user */
        $user = $request->user();
        $searchContext = $searchContextResolver->resolveForUser($token, $user);

        $auditRecorder->record(
            $request,
            'search.result_viewed',
            'success',
            $searchContext,
        );

        $document = $searchContext->document_ciphertext;
        $maskedDocument = str_repeat('•', mb_strlen($document) - 4).mb_substr($document, -4);

        return view('search.result', [
            'token' => $token,
            'documentType' => $searchContext->document_type,
            'maskedDocument' => $maskedDocument,
            'modules' => config('search.modules'),
        ]);
    }
}
