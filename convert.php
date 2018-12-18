<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Output\OutputInterface;

$app = new Silly\Application();

$app->command('fromcsv [filename] [--delimiter=] [--save=]', function ($filename, $delimiter, $save, OutputInterface $output) {
    
    if ( ! empty($filename)) {
        
        if (file_exists($filename)) {
            
            // set delimiter
            $delimiter = ($delimiter != ";") ? "," : $delimiter;
            
            // consider CRLF line breaks and normalize to PHP_EOL
            $csv = implode(PHP_EOL, file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
            
            $Converter = new Converter\Converter();
            
            $Converter->readCSV($csv, $delimiter);
            $xml = $Converter->createXML();
            
            // prettify generated XML
            $dom = new DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($xml);
            
            //save prettified xml to file;
            $save = $save ?: $filename . '.xml';
            
            $dom->save($save);
            
        } else {
            
            $output->writeln("Source file does not exist");
            
        }
        
    } else {
        
        $output->writeln("No source file specified");
        
    }
    
})->descriptions('Transform structured data from CSV to XML format.', [
    'filename' => 'path to the source file',
    '--delimiter' => 'character used to specify the boundary between cells in a line (default: `,`)',
    '--save' => 'path to destination file',
]);

$app->command('fromxml [filename] [--save=]', function ($filename, $save, OutputInterface $output) {
    
    if ( ! empty($filename)) {
        
        if (file_exists($filename)) {
            
            $xml = file_get_contents($filename);
            
            $Converter = new Converter\Converter();
            
            $Converter->readXML($xml);
            $csv = $Converter->createCSV();
            
            //save CSV to file;
            $save = $save ?: $filename . '.csv';
            
            file_put_contents($save, $csv);
            
        } else {
            
            // TODO: colourize
            $output->writeln("Source file does not exist");
            
        }
        
    } else {
        
        // TODO: colourize
        $output->writeln("No source file specified");
        
    }
    
})->descriptions('Transform structured data from XML to CSV format.', [
    'filename' => 'path to the source file',
    '--save' => 'path to destination file',
]);


$app->run();
