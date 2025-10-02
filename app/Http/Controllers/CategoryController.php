<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();
        
        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $categories = $query->orderBy('type')->orderBy('name')->paginate(15);
        
        // Statistics
        $stats = [
            'total_categories' => Category::count(),
            'income_categories' => Category::income()->count(),
            'expense_categories' => Category::expense()->count(),
            'active_categories' => Category::active()->count(),
        ];
        
        return view('categories.index', compact('categories', 'stats'));
    }

    public function create(Request $request)
    {
        $preselectedType = $request->get('type');
        return view('categories.create', compact('preselectedType'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:income,expense',
            'is_active' => 'boolean'
        ]);

        // Check for duplicate names within the same type
        $exists = Category::where('name', $validated['name'])
                         ->where('type', $validated['type'])
                         ->exists();
                         
        if ($exists) {
            return back()->withErrors(['name' => 'A category with this name already exists for this type.']);
        }

        $validated['is_active'] = $request->has('is_active');

        Category::create($validated);

        return redirect()->route('categories.index')
                        ->with('success', 'Category created successfully.');
    }

    public function show(Category $category)
    {
        // Get related records count
        $relatedCount = $category->type === 'income' 
            ? $category->incomes()->count()
            : $category->expenses()->count();
            
        return view('categories.show', compact('category', 'relatedCount'));
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:income,expense',
            'is_active' => 'boolean'
        ]);

        // Check for duplicate names within the same type (excluding current category)
        $exists = Category::where('name', $validated['name'])
                         ->where('type', $validated['type'])
                         ->where('id', '!=', $category->id)
                         ->exists();
                         
        if ($exists) {
            return back()->withErrors(['name' => 'A category with this name already exists for this type.']);
        }

        $validated['is_active'] = $request->has('is_active');

        $category->update($validated);

        return redirect()->route('categories.index')
                        ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        // Check if category is being used
        $relatedCount = $category->type === 'income' 
            ? $category->incomes()->count()
            : $category->expenses()->count();
            
        if ($relatedCount > 0) {
            return back()->withErrors(['error' => 'Cannot delete category that is being used by ' . $relatedCount . ' record(s).']);
        }

        $category->delete();

        return redirect()->route('categories.index')
                        ->with('success', 'Category deleted successfully.');
    }

    public function toggleStatus(Category $category)
    {
        $category->update(['is_active' => !$category->is_active]);
        
        $status = $category->is_active ? 'activated' : 'deactivated';
        
        return back()->with('success', "Category {$status} successfully.");
    }
}