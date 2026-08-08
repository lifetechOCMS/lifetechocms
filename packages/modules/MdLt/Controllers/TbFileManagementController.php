<?php
namespace Lt\Modules\MdLt\Controllers;

use Lt\Modules\MdLt\Services\LtFile; 
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtResponse;

// $siteHostaddress = ltSiteHostAddress();
    //   $homeDirectory = '../v2_8'; 
class TbFileManagementController
{
    // public $homeDirectory = '../v2_8';
    public $homeDirectory = '.';
    
    public function index(){
      
      $folderContent = $this->getFolderContent($this->homeDirectory); 
      $media = $this->mediaFile();
      return  LtResponse::json('success', 201, 200, ['files'=> $folderContent, 'media' => $media]); 

    }
    
    public function mediaFile(){
      $folderName = '/storage/media';
      $directory = $this->homeDirectory.''.$folderName;
      $mediaArr = [];
      $folderContent = $this->getFolderContent($directory, $folderName); 
    //   rebuild the file format
      foreach($folderContent as $media){
          if($media['name'] == 'lifetech_favicon.png'){
              $mediaArr['favicon'] =[
                      'name' => $media['name'],
                      'path' => $media['path'],
                      'size' => $media['size'],
                      'type' => 'image/png',
                  ];
          }else if($media['name'] == 'lifetech_logo.png'){
              $mediaArr['logo'] =[
                      'name' => $media['name'],
                      'path' => $media['path'],
                      'size' => $media['size'],
                      'type' => 'image/png',
                  ];
          }
      }
      
      return $mediaArr;
    }
    
    
    public function navigateToFolder(){
        $request = new LtRequest;
        $folderName = $request->folderName;
        $folderName = $folderName == '/' ? '' : $folderName;
        $directory = $this->homeDirectory.''.$folderName;
        // $directory = $folderName;
        $parentFolder = $folderName;
        $folderContent = $this->getFolderContent($directory, $parentFolder); 
       
        return LtResponse::json('success', 201, 200, $folderContent);
        // return json_encode($folderContent);
    }
    
    public function createItems(){
        $request = new LtRequest;
        $itemName = $request->itemName;
        $itemType = $request->itemType;
        // $path = $this->homeDirectory.''.$request->path;
        $path  =   substr($request->path, 1);
        //$path = ;
        
        if($itemType === 'folder'){
            $response =  LtFile::path($path)->createFolder($itemName);
        }else{
          $response = LtFile::path($path)->createFile($itemName);
        }

        return $response;
    }

    public function deleteItems(){
        $request = new LtRequest;
        $itemName = $request->itemName;
        $itemType = $request->itemType;
        
        $path  =   substr($request->path, 1);
        
        if($itemType === 'folder'){
            $response =  LtFile::path($path)->deleteFolder($itemName);
        }else{
          $response = LtFile::path($path)->deleteFile($itemName);
        }
        return $response;
    }
    
    public function download(){
        $request = new LtRequest;
        $itemName = $request->itemName;
        $itemType = $request->itemType;
        $path = $request->path == '/' ? $this->homeDirectory : $request->path;
        // $path  =   substr($request->path, 1);

        $result = LtFile::path($path)->download($itemName);
        return $result;

    }
    
    
    public function uploads(){
        $request = new LtRequest;
        // $path = $this->homeDirectory.''.$request->path;
        $path  =   substr($request->path, 1);
        $file = $_FILES['file'];
        $overwite = $request->overwrite == '1' ? true : false;
        $response =  LtFile::path($path)->overwrite($overwite)->uploadFile($file);
        return $response;
    }
    
    public function rename(){
        $request = new LtRequest;
        $currentItemName = $request->currentItemName;
        $newItemName = $request->newItemName;
        $itemType = $request->itemType;
        $extension = pathinfo($newItemName, PATHINFO_EXTENSION);
        if($itemType == 'file'){
            if ($extension === ''){
                $newItemName = $newItemName.".txt";
            }
        }
        $path = $this->homeDirectory.''.$request->path;
        $oldPath = $path. DIRECTORY_SEPARATOR . $currentItemName;
        $newPath = $path. DIRECTORY_SEPARATOR . $newItemName;

        if(rename($oldPath, $newPath)){
            return LtResponse::json('file or folder renamed successfully.', 1914, 200, $newItemName);
        }else{
            return LtResponse::json('failed to rename file or folder', 1915, 100);
        }
 
    }
    
    public function readFile(){
        $request = new LtRequest;
        $itemName = $request->itemName;
        $itemType = $request->itemType;
        $path = $this->homeDirectory.''.$request->path;
        
        // Define the file path
        $filePath = $path.'/'.$itemName;
        
        $content = file_get_contents($filePath);
        
        return LtResponse::json('File contents retrieved successfully.', 1916, 200, $content);
        
    }
    
    public function editFileContent(){
        $request = new LtRequest;
        $content = $request->fileContent;
        $filename = $request->fileName;
        $path = $this->homeDirectory.''.$request->path;
        
        $result = LtFile::path($path)->writeFile($filename, $content);
        
        return $result;
    }
    
    
    
    
    public function getFolderContent($directory, $parentFolder = ''){
        
      $newFile = [];
       
    //   $content = file_get_contents($homeDirectory);

      $file = scandir($directory);
      $files = array_slice($file, 2);
       
      foreach ($files as $key => $file){
          $filePath = $parentFolder. DIRECTORY_SEPARATOR . $file;
          $fileDir = substr($filePath, 1);
        //   $fileDir = $this->homeDirectory.''.$filePath;
          if(is_dir($fileDir)){
                $size = $this->getDirectorySize($fileDir);
                $type = 'folder';
          }else if(is_file($fileDir)){
                $size = $this->getFormatFileSize($fileDir);
                $type = 'file';
          }else{
              
              return LtResponse::json('success', 805, 200, $content);
          }
          $newFile[] = ['id' => $key + 1, 'name' => $file, 'size' => $size, 'type' => $type, 'path' => $filePath]; 
           
      }
      
      return $newFile;
        
    }
    
    
    public function getDirectorySize($dirPath){
        if (!is_dir($dirPath)) {
            return "Path is not a directory";
        }
    
        $totalSize = 0;
    
        // Use RecursiveIteratorIterator to go through all files recursively
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dirPath, \FilesystemIterator::SKIP_DOTS)
        );
    
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $totalSize += $file->getSize();
            }
        }
    
        return $this->formatBytes($totalSize);
    }
    
    public function getFormatFileSize($filePath){
        if (!file_exists($filePath)) {
            return "File does not exist";
        }
    
        $bytes = filesize($filePath);
        return $this->formatBytes($bytes);
    }

    private function formatBytes($bytes){
        if ($bytes <= 0) return '0 bytes';
    
        $units = ['bytes', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }

    /// Backend Switching
}