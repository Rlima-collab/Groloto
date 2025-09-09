<?php
declare(strict_types=1);

namespace Classes\Views;

final class Template {
    private string $path;

    private string $layout;

    private string $title;

    private array $cssFiles;

    private string $content;

    public function __construct(string $path) {
        $this->path = $path;
    }

    public function getPath():string {
        return $this->path;
    }

    public function getLayout():string {
        return $this->layout;
    }

    public function getContent():string {
        return $this->content;
    }

    public function getTitle():string {
        return $this->title;
    }

    public function getCssFiles():array {
        return $this->cssFiles;
    }

    public function setLayout(string $layout):self {
        $this->layout = $layout;
        return $this;
    }

    public function setContent(string $content):self {
        $this->content = $content;
        return $this;
    }

    public function setTitle(string $title):self {
        $this->title = $title;
        return $this;
    }

    public function setCssFiles(array $cssFiles):self {
        $this->cssFiles = $cssFiles;
        return $this;
    }

    public function compile():string {
        ob_start();
        $title = $this->getTitle();
        $cssFiles = $this->getCssFiles();
        $content = $this->getContent();
        require sprintf('%s/%s.php', $this->getPath(), $this->getLayout());

        return ob_get_clean();
        
    }
}
?>