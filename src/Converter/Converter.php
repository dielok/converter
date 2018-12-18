<?php
namespace Converter;

class Converter
{
    /**
     * Holds a normalized version of the structured data.
     * @var array
     */
    private $data;
    
    /**
      * Formats a line (passed as a fields array) as CSV and returns the CSV as a string.
      * Adapted from http://us3.php.net/manual/en/function.fputcsv.php#87120
      *
      * @param array $array A two dimensional array representing the data as table (rows in
      *                     first dimension, cells in second dimension)
      * @param string $xml_data Returns the XML object by reference
      * @param integer $layer Tracks array dimension
      *
      * @return string The resulting XML
      */
    protected function arrayToXML(array $array, \SimpleXMLElement &$xml_data, $layer = 0)
    {
        foreach($array as $value) {
            
            $key = ($layer == 0) ? 'row' : 'item';
            
            if(is_array($value)) {
                
                $subnode = $xml_data->addChild($key);
                $this->arrayToXML($value, $subnode, $layer + 1);
                
            } else {
                
                $xml_data->addChild($key, htmlspecialchars($value));
                
            }
            
        }
    }
    
    /**
      * Formats lines (from data array) as CSV and returns the concatenated CSV as a string.
      * Adapted from http://us3.php.net/manual/en/function.fputcsv.php#87120
      *
      * @param string $delimiter Character used to specify the boundary between cells in a line
      * @param string $enclosure Character used to specify groups of content that can be misinterpreted otherwise
      *
      * @return string The resulting CSV
      */
    protected function arrayToCSV(array $array, $delimiter = ',', $enclosure = '"')
    {
        $delimiterMasq = preg_quote($delimiter, '/');
        $enclosureMasq = preg_quote($enclosure, '/');
        
        $csv_lines = [];
        
        foreach ($array as $row) {
            
            foreach ($row as &$cell) {
                
                // Enclose fields containing $delimiter, $enclosure or whitespace
                if (preg_match("/(?:${delimiterMasq}|${enclosureMasq}|\s)/", $cell)) {
                    
                    $cell = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $cell) . $enclosure;
                    
                }
                
            }
            
            $csv_lines[] = implode($delimiter, $row);
            
        }
        
        return implode(PHP_EOL, $csv_lines);
    }
    
    /**
      * Iterates over the \SimpleXMLElement to get a pure array
      * (possible since \SimpleXMLElement implements the Traverable interface).
      *
      * @param \SimpleXMLElement $xml The XML content
      *
      * @return string The resulting data array
      */
    protected function XMLToArray(\SimpleXMLElement $xml)
    {
        $array = [];
        
        foreach ($xml as $row) {
            
            $children = [];
            
            foreach ($row as $item) {
                
                $children[] = (string)$item;
                
            }
            
            $array[] = $children;
            
        }
        
        return $array;
    }
    
    /**
      * Iterates over the \SimpleXMLElement to get a pure array
      * (possible since \SimpleXMLElement implements the Traverable interface).
      *
      * @param string $csv A raw CSV string (expects each line to be separated by PHP_EOL)
      * @param string $delimiter Character used to specify the boundary between cells in a line
      *
      * @return string the resulting data array
      */
    protected function CSVToArray($csv, $delimiter = null)
    {
        $array = [];
        $lines = explode(PHP_EOL, $csv);
        
        foreach ($lines as $line) {
            
            $array[] = str_getcsv($line, $delimiter);
            
        }
        
        return $array;
    }
    
    /**
      * Convenience method: stores array transformed XML into data property.
      *
      * @param string $xml A raw XML string
      *
      * @return void
      */
    public function readXML($xml)
    {
        $xml_object = new \SimpleXMLElement($xml);
        
        $this->setData($this->XMLToArray($xml_object));
    }
    
    /**
      * Convenience method: stores array transformed CSV into data property.
      *
      * @param string $csv A raw CSV string
      * @param string $delimiter Character used to specify the boundary between cells in a line
      *
      * @return void
      */
    public function readCSV($csv, $delimiter = null)
    {
        $this->setData($this->CSVToArray($csv, $delimiter));
    }
    
    /**
      * Convenience method: Invokes either XML or CSV parsing by string.
      *
      * @param string $format A command string to choose either `xml` or `csv`
      * @param string $raw The raw structured data
      *
      * @return boolean
      */
    public function read($format, $raw)
    {
        if (strtolower($format) == 'xml') {
            
            return $this->readXML($raw);
            
        } elseif ($format === 'csv') {
            
            return $this->readCSV($raw);
            
        }
        
        return false;
    }
    
    /**
      * Convenience method: Uses `arrayToXML` to transform the data from array to XML and returns it.
      *
      * @return string
      */
    public function createXML()
    {
        $xml_data = new \SimpleXMLElement('<?xml version="1.0"?><data></data>');
        
        $this->arrayToXML($this->getData(), $xml_data);
        
        return $xml_data->asXML();
    }
    
    /**
      * Convenience method: Uses `arrayToCSV` to transform the data from array to CSV and returns it.
      *
      * @param string $delimiter Character used to specify the boundary between cells in a line
      *
      * @return string
      */
    public function createCSV($delimiter = ',')
    {
        $data = $this->getData();
        
        return $this->arrayToCSV($data, $delimiter);
    }
    
    // I normally do avoid writing naive setters and getters, but I
    // do it here, since it is sometimes considered good practice
    // source: https://web.archive.org/web/20140625191431/https://developers.google.com/speed/articles/optimizing-php
    public function setData($data)
    {
        $this->data = $data;
    }
    
    public function getData()
    {
        return $this->data;
    }
}