<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Audit\AuditRecorder;
use App\Services\Search\SearchContextResolver;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchModuleController extends Controller
{
    public function show(
        Request $request,
        string $token,
        string $module,
        SearchContextResolver $searchContextResolver,
        AuditRecorder $auditRecorder,
    ): View {
        /** @var array{label: string, permission: string}|null $moduleConfiguration */
        $moduleConfiguration = config('search.modules.'.$module);
        abort_if($moduleConfiguration === null, 404);

        /** @var User $user */
        $user = $request->user();
        $searchContext = $searchContextResolver->resolveForUser($token, $user);

        if (! $user->can($moduleConfiguration['permission'])) {
            $auditRecorder->record(
                $request,
                'search.module_viewed',
                'forbidden',
                $searchContext,
                $module,
            );

            abort(403);
        }

        $auditRecorder->record(
            $request,
            'search.module_viewed',
            'unavailable',
            $searchContext,
            $module,
        );

        return view('search.partials.module-state', [
            'state' => 'unavailable',
            'moduleLabel' => $moduleConfiguration['label'],
        ]);
    }
}
