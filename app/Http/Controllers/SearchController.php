<?php

namespace App\Http\Controllers;

use App\Actions\Search\CreateSearchContext;
use App\Http\Requests\StoreSearchRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(): View
    {
        return view('search.index');
    }

    public function store(StoreSearchRequest $request, CreateSearchContext $createSearchContext): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $result = $createSearchContext->handle(
            $user,
            $request->string('document')->toString(),
            $request,
        );

        return redirect()
            ->route('search.results.show', ['token' => $result['token']])
            ->setStatusCode(303);
    }
}
