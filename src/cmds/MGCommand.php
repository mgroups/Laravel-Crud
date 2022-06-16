<?php

namespace MGroups\MGcrud\cmds;
// TODO: show successful msg after enter or edit or delete & remove view in index when show is no

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
//use Symfony\Component\Console\Input\InputOption;
use Illuminate\Support\Str;
use InvalidArgumentException;
use MGroups\MGcrud\lib\MGFactory;
use MGroups\MGcrud\lib\MGMakeViews;
use MGroups\MGcrud\lib\MGNames;
use Symfony\Component\Console\Input\InputOption;


/*
 * base_path
 * TODO: add folder option to view
 * */

class MGCommand extends Command
{

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mg:create {name : Model Name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Laravel CRUD';
    private $fillable;
    private $needShowPage = false;
    private $needMigration = false;
    private $needPolicy = false;
    private $needFactory = false;

    /**
     * Create a new command instance.
     *
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;

    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {

        //$name = $this->ask('What is your name?');

        $name = $this->argument('name');

        $modelName = $this->argument('name');



/*        if($this->option('migration'))
        {
            $this->needMigration = true;
        }*/

        $this->setFable($modelName);


        if ($this->confirm('Do you want Policy?'))
        {
            $this->needPolicy = true;
            $this->call('make:policy', [
                'name' => $modelName."Policy", '--model' => $modelName
            ]);
        }

        if ($this->confirm('Do you want Controller?'))
        {
            $this->createController($modelName);
        }

        if ($this->confirm('Do you want Migration?'))
        {
            if($this->confirm('Need to Migrate?'))
            {
                $this->needMigration = true;
                $this->createMigration($modelName);
            }else {
                $this->createMigration($modelName);
            }
        }

        if ($this->confirm('Add Resource Route?'))
        {
            $this->addResourceRoute($modelName);
        }

        if ($this->confirm('Do you want Views?'))
        {

            $viewStyle = $this->choice('Select View Style....', ['Normal', 'Horizontal'], 0);
            // TODO: need folder
            if($this->confirm('with Show?'))
            {
                $this->needShowPage = true;
                $this->makeViews($modelName, $viewStyle);
            }else {
                $this->makeViews($modelName, $viewStyle);
            }

        }

        if ($this->confirm('Need to Add Nav Links to App Layout?'))
        {
            $this->addNavLinks($modelName);
        }

        if($this->confirm('Need Fake Data..?'))
        {
            $this->makeFactory($modelName);
        }

    }

    /**
     * Get the stub file for the generator.
     *
     * @param $arg
     * @return string
     */
    protected function getStub($arg = NULL)
    {
        if($arg == 'migration')
        {
            return __DIR__.'./../stubs/mg_migration.php';
        }
        return __DIR__.'./../stubs/DummyClassController.php';
    }

    private function createController($name)
    {

        $MGUpdateInputs = ""; $MGPolicy = ""; $MGAuthorImport = ""; $MGAuthorExcept = "";

        if($this->needPolicy)
        {
            $MGPolicy = "\n\t\t@#@this->authorize('create', ".$name."::class);";
            $MGPolicy = $this->setDollorSign($MGPolicy);
            $MGAuthorImport = "use Illuminate\Auth\Access\AuthorizationException;";
            $MGAuthorExcept = "@throws AuthorizationException";
        }

        foreach ($this->fillable as $val)
        {
            $MGUpdateInputs .= "@#@".Str::camel($name)."->".$val." = @#@request->input('".$val."');\n\t\t";

            //$MGUpdateInputs = str_replace('@#@', '$', $MGUpdateInputs);
            $MGUpdateInputs = $this->setDollorSign($MGUpdateInputs);
        }

        try {

            $stub = str_replace(
                ['DummyClass', 'MGVariableModel', 'DummyRootNamespace', 'NamespacedDummyUserModel', '//MGUpdateInputs', '//MGPolicy', '//MGAuthorImport', '//MGAuthorExcept'],
                [$name, Str::camel($name), $this->rootNamespace(), $this->userProviderModel(), $MGUpdateInputs, $MGPolicy, $MGAuthorImport, $MGAuthorExcept],
                $this->files->get($this->getStub())
            );

            $this->files->put(app_path("/Http/Controllers/" . $name . "Controller.php"), $stub);

            $this->info('Controller created successfully');

        } catch (FileNotFoundException $e) {
            $this->error("File Not Found");
        }
    }



    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     * @throws FileNotFoundException
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }

    /**
     * Get the full namespace for a given class, without the class name.
     *
     * @param  string  $name
     * @return string
     */
    protected function getNamespace($name)
    {
        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }



    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name);

        return str_replace('DummyClass', $class, $stub);
    }


    /**
     * Determine if the class already exists.
     *
     * @param  string  $rawName
     * @return bool
     */
    protected function alreadyExists($rawName)
    {
        return $this->files->exists($this->getPath($this->qualifyClass($rawName)));
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return $this->laravel['path'].'/'.str_replace('\\', '/', $name).'.php';
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function rootNamespace()
    {
        return $this->laravel->getNamespace();
    }



    /**
     * Parse the class name and format according to the root namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function qualifyClass($name)
    {
        $name = ltrim($name, '\\/');

        $rootNamespace = $this->rootNamespace();

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        $name = str_replace('/', '\\', $name);

        return $this->qualifyClass(
            $this->getDefaultNamespace(trim($rootNamespace, '\\')).'\\'.$name
        );
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace;
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name)
    {
        $stub = str_replace(
            ['DummyNamespace', 'DummyRootNamespace', 'NamespacedDummyUserModel'],
            [$this->getNamespace($name), $this->rootNamespace(), $this->userProviderModel()],
            $stub
        );

        return $this;
    }

    /**
     * Get the model for the default guard's user provider.
     *
     * @return string|null
     */
    protected function userProviderModel()
    {
        $guard = config('auth.defaults.guard');

        $provider = config("auth.guards.{$guard}.provider");

        return config("auth.providers.{$provider}.model");
    }

    protected function setFablesPublic($modelName)
    {
        $den = "";
        try {
            $den = $this->files->get(app_path() . "\\Models\\" . $modelName . ".php");
        } catch (FileNotFoundException $e) {
            $this->error('Model Not Found');
        }

        $nco = str_replace('protected $fillable', 'public $fillable', $den);

        $this->files->put(app_path()."\\Models\\".$modelName.".php", $nco);
    }

    protected function setFablesPrivate($modelName)
    {
        $den = "";
        try {
            $den = $this->files->get(app_path() . "\\Models\\" . $modelName . ".php");
        } catch (FileNotFoundException $e) {
            $this->error('Model Not Found');
        }

        $nco = str_replace('public $fillable','protected $fillable',  $den);

        $this->files->put(app_path()."\\Models\\".$modelName.".php", $nco);

    }

    protected function setFable($modelName)
    {
        $this->setFablesPublic($modelName);
        $modelClass = $this->parseModel($modelName);

        $a = new $modelClass();

        $this->fillable = $a->fillable;

        $this->setFablesPrivate($modelName);

        // $this->info(implode(" ", $this->fillable));

    }

    /**
     * Get the fully-qualified model class name.
     *
     * @param  string  $model
     * @return string
     *
     * @throws InvalidArgumentException
     */
    protected function parseModel($model)
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $model)) {
            throw new InvalidArgumentException('Model name contains invalid characters.');
        }

        $model = trim(str_replace('/', '\\', $model), '\\');

        if (! Str::startsWith($model, $rootNamespace = $this->laravel->getNamespace())) {
            $model = $rootNamespace."Models\\".$model;
        }

        return $model;
    }

    private function makeViews($name, $viewStyle)
    {
        new MGMakeViews($name, $this->fillable, $this->files, $this->needShowPage, $viewStyle);

        $this->info('View are created');
    }

    public function addResourceRoute($name)
    {
        /**/

        $modelKebab = Str::kebab($name);

        $insert_data =   '
Route::resource(\'/' . $modelKebab . "', '{$name}Controller');
        ";


        $check_data = 'Route::resource(\'/' . $modelKebab . "', {$name}Controller::class);";

        $mg_routers = $this->files->get(base_path('routes/web.php'));

        if(Str::contains($mg_routers, $check_data))
        {
            $this->warn('Resource Already Exists in web routes file');
        }
        else {
             $this->files->append(base_path('routes/web.php'), $insert_data);
        }

        $this->info('Resource added in Web Routes');


    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['policy', 'p', InputOption::VALUE_NONE, 'Generate Policy']
        ];
    }

    private function setDollorSign(String $content)
    {
        return str_replace('@#@', '$', $content);

    }

    private function addNavLinks(String $name)
    {
        $model_title = trim(preg_replace('/(?<=\\w)(?=[A-Z])/'," $1", $name)); $class_name = $name; $appLayout = ""; $name = Str::kebab($name); $m_template = "";

        try {
            $appLayout = $this->files->get(resource_path('views/layouts/app.blade.php'));
        } catch (FileNotFoundException $e) {
        }

        if($this->needPolicy){

            $MGCreateLi = '@can(\'create\', App\\'.$class_name.'::class)
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="/'.$name.'/create">'.$model_title.'</a>
                                @endcan
                                {{-- MGENTRIES --}}';

            $MGReportLi = '@can(\'create\', App\\'.$class_name.'::class)
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="/'.$name.'/">'.$model_title.'</a>
                                @endcan
                                {{-- MGREPORTS --}}';

        }else

            $MGCreateLi = '<div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="/'.$name.'/create">'.$model_title.'</a>
                                {{-- MGENTRIES --}}';

            $MGReportLi = '<div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="/'.$name.'/">'.$model_title.'</a>
                                {{-- MGREPORTS --}}';{

        }

        if(Str::contains($appLayout, $MGCreateLi))
        {
            $this->warn($name." CREATE LINK ALREADY EXISTS");
            $m_template = $appLayout;
        }
        else {
            $m_template = str_replace(
                ['{{-- MGENTRIES --}}', '{{-- MGREPORTS --}}'],
                [$MGCreateLi, $MGReportLi],
                $appLayout);
        }

        file_put_contents(resource_path('views/layouts/app.blade.php'), $m_template);

        $this->info('Nav Links added in app layout');
    }

    private function createMigration(String $name)
    {
        $mg_content = ""; $m_template = "";

        foreach ($this->fillable as $val) {
            $mg_content .= "@#@table->string('".$val."')->nullable();\n\t\t\t";
        }

        $mg_content = $this->setDollorSign($mg_content);

        try {
            $m_template = $this->files->get($this->getStub('migration'));
        } catch (FileNotFoundException $e) {
        }

        $stub = str_replace(
            ['//MGColumns', 'MGClass', 'MGTableName'],
            [$mg_content, 'Create'.$name.'Table', MGNames::getTableName($name)],
            $m_template
        );

        $this->files->put(base_path('database/migrations/'.$this->getDatePrefix().'_create_'.MGNames::getTableName($name).'_table.php'), $stub);

        $this->info('Migration Created');

        if($this->needMigration)
        {
            $this->call('migrate');

            $this->info('Migration migrated');
        }



    }

    /**
     * Get the date prefix for the migration.
     *
     * @return string
     */
    protected function getDatePrefix()
    {
        return date('Y_m_d_His');
    }

    public function makeFactory(String $modelName)
    {
        $this->needFactory = true;
        $this->call('make:factory', [
            'name' => $modelName."Factory", '--model' => $modelName
        ]);

        new MGFactory($modelName, $this->fillable, $this->files);
    }



}
