<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Services\SearchService;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(): View
    {
        return view('search.index');
    }

    public function store(SearchRequest $request, SearchService $searchService): View
    {
        $document = $request->string('documento')->toString();

        return view('search.result', [
            'document' => $document,
            'result' => $searchService->search($document),
        ]);
    }
}
