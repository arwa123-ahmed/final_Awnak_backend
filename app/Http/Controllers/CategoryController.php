<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Service;


class CategoryController extends Controller
{

    public function storeCategory(Request $request)
    {
        $request->validate([
            'en_name' => 'required|string|max:255',
            'ar_name' => 'required|string|max:255',
            'mode' => 'required|in:online,offline',
            'en_description' => 'required|string|max:255',
            'ar_description' => 'required|string|max:255'
        ]);
        

        $input = $request->all();

        Category::create($input);
         return response()->json([
            'message' => 'Category added successfully',
            'category' => $input,
        ]);

    }

    public function editCategory (Request $request , $id)
    {
        $category = Category::find($id);
        $request->validate([
            'en_name' => 'required|string|max:255',
            'ar_name' => 'required|string|max:255',
            'mode' => 'required|in:online,offline',
            'en_description' => 'required|string|max:255',
            'ar_description' => 'required|string|max:255'
        ]);

        $updatedCategory = $request->all();
         $category->update($updatedCategory);

          return response()->json([
            'message' => 'category updated successfully',
            'updatedCategory' => $updatedCategory,
        ]);
    }

    public function deleteCategory($id)
    {
        $category = Category::find($id);
        $category->delete();
        return response()->json([
            'message' => 'Category Deleted successfully',
        ]);
    }

    public function index()
    {
      $categories = Category::all();
      return response()->json([
          'categories' => $categories
      ]);
    }
    
    public function showCategory(Request $request)
    {
        if ($request->mode == 'offline') {
        $category = Category::where('mode', 'offline')->get();
        } 
        elseif ($request->mode == 'online') {
        $category = Category::where('mode', 'online')->get();
        }
        return response()->json([
            'message' => 'category retrieved successfully',
            'categories' => $category
        ]);

    }

    }
