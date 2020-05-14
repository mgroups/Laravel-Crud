<?php


namespace MGroups\MGcrud\lib;


use Illuminate\Filesystem\Filesystem;

class MGFactory
{

    public $name = "";
    public $fillable = [];

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * MGFactory constructor.
     * @param string $name
     * @param array $fillable
     * @param Filesystem $files
     */
    public function __construct(string $name, array $fillable, Filesystem $files)
    {
        $this->name = $name;
        $this->fillable = $fillable;
        $this->files = $files;

        $this->setContent();
    }

    public function setContent()
    {
        $mgContent = "\t";

        foreach ($this->fillable as $val)
        {
            $mgContent .= "'$val' => @#@faker->".$val.",
            ";
        }

        $mg_content2 = $this->files->get(base_path('database/factories/'.$this->name.'Factory.php'));

        $mgContent = $this->setDollorSign($mgContent);

        $stub = str_replace(
            ['//'],
            [$mgContent],
            $mg_content2
        );

        $this->files->put(base_path('database/factories/'.$this->name.'Factory.php'), $stub);

        $this->addFactoryToDatabaseSeed();

    }

    private function setDollorSign(String $content)
    {
        return str_replace('@#@', '$', $content);
    }

    public function addFactoryToDatabaseSeed()
    {

        $source = $this->files->get(base_path('database/seeds/DatabaseSeeder.php'));
        $target = "// @#@this->call(UserSeeder::class);\n\t\tfactory(".$this->name."::class, 10)->create();";
        $target = $this->setDollorSign($target);


        $stub = str_replace(
            ['// $this->call(UserSeeder::class);'],
            [$target],
            $source
        );


        $this->files->put(base_path('database/seeds/DatabaseSeeder.php'), $stub);

    }

}
