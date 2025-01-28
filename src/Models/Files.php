<?php

namespace TomatoPHP\FilamentBrowser\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Sushi\Sushi;

class Files extends Model
{
    use Sushi;

    public function getRows(): array
    {
        $filterFiles = filament('filament-browser')->hiddenFiles;
        $filterFolders = filament('filament-browser')->hiddenFolders;
        $filterExtensions = filament('filament-browser')->hiddenExtensions;

        $root = session()->has('filament-browser-path') ? session()->get('filament-browser-path') : (filament('filament-browser')->basePath?: config('filament-browser.start_path'));
        $getFolders = File::directories($root, true);
        $getFiles = File::files($root, true);
        $browser = [];
        foreach ($getFolders as $folder){
            if(!in_array($folder, $filterFolders)){
                $browser[] = [
                    "name" => str($folder)->remove($root.'/')->toString(),
                    "folder" => $root,
                    "path" => $folder,
                    "type" => "folder",
                    "size" => "0",
                    "extension" => "folder"
                ];
            }
        }
        foreach ($getFiles as $file){
            if((!in_array($file->getRealPath(), $filterFiles)) && (!in_array($file->getExtension(), $filterExtensions))){
                $totalSize = $file->getSize();
                if($totalSize<1000){
                    $totalSize = $totalSize. 'bytes';
                }
                else if($totalSize<100000){
                    $totalSize = ($totalSize/1000). 'KB';
                }
                else if($totalSize<1000000){
                    $totalSize = ($totalSize/1000). 'KB';
                }
                else if($totalSize<1000000000){
                    $totalSize = ($totalSize/1000000). 'MB';
                }
                else if($totalSize>1000000000){
                    $totalSize = ($totalSize/1000000). 'GB';
                }

                $browser[] = [
                    "name" => $file->getFilename(),
                    "folder" => $root,
                    "path" => $file->getRealPath(),
                    "type" => "file",
                    "size" => $totalSize,
                    "extension" => $file->getExtension()??"file",
                ];
            }

        }

        return $browser;
    }

}
