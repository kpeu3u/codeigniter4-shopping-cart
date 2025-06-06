<?php

declare(strict_types=1);

namespace ShoppingCart\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Autoload;
use Exception;

class CartCommand extends BaseCommand
{
    /**
     * The group the command is lumped under
     * when listing commands.
     *
     * @var string
     */
    protected $group = 'Shopping Cart';

    /**
     * The command's name.
     *
     * @var string
     */
    protected $name = 'cart:publish';

    /**
     * The command's short description.
     *
     * @var string
     */
    protected $description = 'Publish config cart to folder App\Config';

    /**
     * The command's usage.
     *
     * @var string
     */
    protected $usage = 'cart:publish';

    /**
     * The path directory.
     */
    protected string $sourcePath;

    /**
     * Displays the help for the spark cli script itself.
     */
    public function run(array $params): void
    {
        $this->determineSourcePath();
        $this->publishConfig();
    }

    protected function publishConfig(): void
    {
        $path = "{$this->sourcePath}/Config/Cart.php";

        $content = file_get_contents($path);
        $content = str_replace('namespace ShoppingCart\Config', 'namespace Config', $content);

        $this->writeFile('Config/Cart.php', $content);
    }

    // --------------------------------------------------------------------
    // Utilities
    // --------------------------------------------------------------------

    /**
     * Replaces the Myth\Auth namespace in the published
     * file with the applications current namespace.
     */
    protected function replaceNamespace(string $contents, string $originalNamespace, string $newNamespace): string
    {
        $appNamespace      = APP_NAMESPACE;
        $originalNamespace = "namespace {$originalNamespace}";
        $newNamespace      = "namespace {$appNamespace}\\{$newNamespace}";

        return str_replace($originalNamespace, $newNamespace, $contents);
    }

    /**
     * Determines the current source path from which all other files are located.
     */
    protected function determineSourcePath(): void
    {
        $this->sourcePath = realpath(__DIR__ . '/../');

        if ($this->sourcePath === '/' || empty($this->sourcePath)) {
            CLI::error('Unable to determine the correct source directory. Bailing.');

            exit();
        }
    }

    /**
     * Write a file, catching any exceptions and showing a
     * nicely formatted error.
     */
    protected function writeFile(string $path, string $content): void
    {
        $config  = new Autoload();
        $appPath = $config->psr4[APP_NAMESPACE];

        $directory = dirname($appPath . $path);

        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        try {
            write_file($appPath . $path, $content);
        } catch (Exception $e) {
            $this->showError($e);

            exit();
        }

        $path = str_replace($appPath, '', $path);

        CLI::write(CLI::color('  created: ', 'green') . $path);
    }
}
