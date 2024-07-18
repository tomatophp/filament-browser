<?php

namespace TomatoPHP\FilamentBrowser\Excel;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class FileImport implements ToCollection
{
    /**
     * @param  Collection  $collection
     */
    public function collection(Collection $collection)
    {
        return $collection;
    }
}
