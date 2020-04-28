<?php


namespace MGroups\MGcrud\lib;


use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

/* TODO: add additional fields*/
class MGMakeViews
{

   public $name = "";
   public $fillable = [];
   public $needShowFile = false;
   public $viewStyle = "";

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * MGMakeViews constructor.
     * @param String $name
     * @param array $fillable
     * @param Filesystem $files
     */
    public function __construct(String $name, Array $fillable, Filesystem $files, $needShowFile, $viewStyle)
    {
        $this->name = $name;
        $this->fillable = $fillable;
        $this->files = $files;
        $this->needShowFile = $needShowFile;
        $this->viewStyle = $viewStyle;

        $this->createView();
        $this->indexView();

        if($this->needShowFile)
        {
            $this->showView();
        }
    }

    public function createView()
    {

        // TODO MGModel to title
        $mgInput = ""; $modelKebab = ""; $modelVar = "";

        foreach ($this->fillable as $val) {

            $val2 = str_replace('_', ' ', $val);

            $val2 = Str::title($val2);

            $modelKebab = Str::kebab($this->name);
            $modelVar = MGNames::getVarName($this->name);


                if($this->viewStyle == 'Normal')
                {
                    $mgInput .= '<div class="form-group">
                                                <label for="'.$val.'">'.$val2.'</label>
                                                <input type="text" class="form-control @error(\''.$val.'\') is-invalid @enderror" id="'.$val.'" name="'.$val.'" placeholder="'.$val2.'" @if($a) value="{{$'.$modelVar.'->'.$val.'}}" @endif required>
                                                @error(\''.$val.'\')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            ';
                }else {
                    $mgInput .= '<div class="form-group row">
                                                <label for="'.$val.'" class="col-sm-4 col-form-label">'.$val2.'</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control @error(\''.$val.'\') is-invalid @enderror" id="'.$val.'" name="'.$val.'" placeholder="'.$val2.'" @if($a) value="{{$'.$modelVar.'->'.$val.'}}" @endif required>
                                                    @error(\''.$val.'\')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            ';
                }

            }


        $mg_content = $this->files->get($this->getStub('create'));

        $m_template = str_replace(
            ['MGModel', '@@MGInputs', 'MGKebabModel', 'MGVariableModel', 'MGCallWord'],
            [$this->name, $mgInput, $modelKebab, $modelVar, MGNames::getNormalCallWord($this->name)],
            $mg_content);

        if($this->files->makeDirectory(resource_path('views/'.$this->name), 0777, true, true))
        {
            file_put_contents(resource_path('views/'.$this->name."/{$this->name}_create.blade.php"), $m_template);

        }else {

            file_put_contents(resource_path('views/'.$this->name."/{$this->name}_create.blade.php"), $m_template);

        }
    }

    public function indexView()
    {
        $MGTableHeads = ""; $modelKebab = ""; $modelVar = ""; $MGTableData = "";

        foreach ($this->fillable as $val) {

            $val2 = str_replace('_', ' ', $val);

            $val2 = Str::title($val2);

            $modelKebab = Str::kebab($this->name);
            $modelVar = Str::camel($this->name);

            $MGTableHeads .= '<th>'.$val2.'</th>
                        ';

            $MGTableData .= '<td>{{ $'.$modelVar.'->'.$val.'}}</td>
                            ';

        }


        $mg_content = $this->files->get($this->getStub('index'));

        $m_template = str_replace(
            ['MGModel', '@@MGTableHeads', 'MGKebabModel', 'MGVariableModel', '@@MGTableData', 'MGCallWord'],
            [$this->name, $MGTableHeads, $modelKebab, $modelVar, $MGTableData, MGNames::getNormalCallWord($this->name)],
            $mg_content);

        if($this->files->makeDirectory(resource_path('views/'.$this->name), 0777, true, true))
        {

            file_put_contents(resource_path('views/'.$this->name."/{$this->name}_index.blade.php"), $m_template);

        }else {

            file_put_contents(resource_path('views/'.$this->name."/{$this->name}_index.blade.php"), $m_template);

        }

    }

    public function showView()
    {
        file_put_contents(resource_path('views/'.$this->name."/{$this->name}_show.blade.php"), 'your own content');
    }

    private function getStub(string $view)
    {
       if($view == 'create')
       {
           return __DIR__ . './../stubs/mgViews/single.create.blade.php';
       }
       else if($view == 'index')
       {
           return __DIR__ . './../stubs/mgViews/index.blade.php';
       }

       return NULL;
    }
}
