<?php

namespace App\Http\Controllers;

use App\Models\Snippet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SnippetController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->snippets();
        
        if ($request->has('language')) {
            $query->where('language', $request->language);
        }
        
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('code', 'like', "%{$searchTerm}%");
            });
        }
        
        if ($request->has('favorites') && $request->favorites) {
            $query->where('is_favorite', true);
        }
        
        $snippets = $query->orderBy('created_at', 'desc')->get();
        
        return response()->json(['snippets' => $snippets]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'required|string',
            'language' => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        $snippet = $request->user()->snippets()->create([
            'title' => $request->title,
            'description' => $request->description,
            'code' => $request->code,
            'language' => $request->language,
            'is_favorite' => $request->has('is_favorite') ? $request->is_favorite : false,
        ]);

        return response()->json([
            'message' => 'Snippet created successfully',
            'snippet' => $snippet
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $snippet = $request->user()->snippets()->findOrFail($id);
        
        return response()->json(['snippet' => $snippet]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string',
            'language' => 'sometimes|required|string|max:50',
            'description' => 'nullable|string',
            'is_favorite' => 'sometimes|boolean',
        ]);

        $snippet = $request->user()->snippets()->findOrFail($id);
        $snippet->update($request->all());

        return response()->json([
            'message' => 'Snippet updated successfully',
            'snippet' => $snippet
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $snippet = $request->user()->snippets()->findOrFail($id);
        $snippet->delete();

        return response()->json(['message' => 'Snippet deleted successfully']);
    }

    public function toggleFavorite(Request $request, $id)
    {
        $snippet = $request->user()->snippets()->findOrFail($id);
        $snippet->is_favorite = !$snippet->is_favorite;
        $snippet->save();

        return response()->json([
            'message' => $snippet->is_favorite ? 'Snippet added to favorites' : 'Snippet removed from favorites',
            'is_favorite' => $snippet->is_favorite
        ]);
    }
}