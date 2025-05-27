<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\StudentsImport;
use Maatwebsite\Excel\Facades\Excel;

class StudentImportController extends Controller
{
    // Show the form to upload the Excel/CSV file
    public function showImportForm()
    {
        return view('admin.students.import'); // Make sure this view exists
    }

    // Handle the import POST request
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv'
        ]);

        Excel::import(new StudentsImport, $request->file('file'));

        return redirect()->back()->with('success', 'Students imported successfully!');
    }
}
