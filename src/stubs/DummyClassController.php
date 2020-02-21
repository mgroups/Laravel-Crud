<?php


namespace App\Http\Controllers;

use App\DummyClass;
use Illuminate\Http\Request;
use Illuminate\View\View;
//MGAuthorImport

class DummyClassController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return View
     * //MGAuthorExcept
     */
    public function index()
    {//MGPolicy
        $MGVariableModels = DummyClass::latest()->paginate(15);
        return view('DummyClass.DummyClass_index', ['MGVariableModels' => $MGVariableModels, 'showPagination' => true]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     * //MGAuthorExcept
     */
    public function create()
    {//MGPolicy
        return view('DummyClass.DummyClass_create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return View
     */
    public function store(Request $request)
    {
        DummyClass::create($request->all());
        return view('DummyClass.DummyClass_create');
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return View
     * //MGAuthorExcept
     */
    public function show($id)
    {//MGPolicy
        $MGVariableModel =  DummyClass::find($id);
        return view('DummyClass.DummyClass_show', ['MGVariableModel' => $MGVariableModel]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $id
     * @return View
     * //MGAuthorExcept
     */
    public function edit($id)
    {//MGPolicy
        $MGVariableModel =  DummyClass::find($id);
        return view('DummyClass.DummyClass_create', ["MGVariableModel" => $MGVariableModel]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $id
     * @return View
     */
    public function update(Request $request, $id)
    {
        $MGVariableModel = DummyClass::find($id);

        //MGUpdateInputs

        $MGVariableModel->save();

        return view('DummyClass.DummyClass_edit', ["MGVariableModel" => $MGVariableModel]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return View
     * //MGAuthorExcept
     */
    public function destroy($id)
    {//MGPolicy
        $DummyClass = DummyClass::find($id);

        $DummyClass->delete();

        $DummyClasss = DummyClass::all();

        return view('DummyClass.DummyClass_index', ['DummyClasss' => $DummyClasss]);
    }
}
