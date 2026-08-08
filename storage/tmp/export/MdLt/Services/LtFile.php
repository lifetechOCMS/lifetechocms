<?php
namespace Lt\Modules\MdLt\Services;

use Lt\Modules\MdLt\Services\LtResponse;

use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class LtFile
{
    /*** MODULE RESPONSE CODES (1900–2000 reserved for File Management) ***/
    private const RC_BASE_PATH_NOT_FOUND   = '1901';
    private const RC_INVALID_UPLOAD        = '1902';
    private const RC_SIZE_EXCEEDED         = '1903';
    private const RC_EXT_NOT_ALLOWED       = '1904';
    private const RC_SECURITY_CHECK_FAIL   = '1905';
    private const RC_MOVE_FAILED           = '1906';
    private const RC_FILE_EXISTS           = '1907';
    private const RC_PARENT_DIR_NOT_FOUND  = '1908';
    private const RC_FILE_NOT_FOUND        = '1909';
    private const RC_DIR_EXISTS            = '1910';
    private const RC_DIR_CREATE_FAILED     = '1911';
    private const RC_DIR_NOT_FOUND         = '1912';
    private const RC_DIR_DELETE_FAILED     = '1913';

    private const RC_FILE_UPLOADED         = '1991';
    private const RC_FILE_CREATED          = '1992';
    private const RC_FILE_WRITTEN          = '1993';
    private const RC_FILE_DELETED          = '1994';
    private const RC_DIR_CREATED           = '1995';
    private const RC_DIR_DELETED           = '1996';
    private const RC_ZIP_CREATION_FAILED   = '1997';
    private const RC_ZIP_CREATED           = '1998';

    /*** STATE ***/
    protected string  $basePath;            // required via static path()
    protected ?int    $maxSizeBytes = null; // from ->size()
    protected bool    $overwrite    = false;
    protected array   $allowedExt   = [];   // from ->ext()

    /*** ENTRYPOINT ***/
    public static function path(string $basePath="",string $createPathIfNotExist = ''): self
    {
        $self = new self();
        if ($basePath === '') {
            $basePath = '.';
        }
        $self->basePath = rtrim($basePath, '/\\') . DIRECTORY_SEPARATOR;
        
        if (substr($self->basePath, 0, 1) === '/') {
         $self->basePath = substr($self->basePath, 1);
        }
        
        // auto-create folder if requested
        if (strtolower($createPathIfNotExist) === 'yes' && !is_dir($self->basePath)) {
            mkdir($self->basePath, 0777, true);
        }
        
        //remove last slash when is not directory
        if (!is_dir($self->basePath)) {
           $self->basePath = rtrim($self->basePath, '/\\') ; 
        }
            
        
        return $self;
    }

    /*** CHAINS ***/
    // Accepts "300KB", "2MB", "1GB", "1024B" or raw bytes
    public function size($value): self
    {
        if (is_numeric($value)) {
            $this->maxSizeBytes = (int)$value;
            return $this;
        }

        $v = strtoupper(trim((string)$value));
        if (substr($v, -2) === 'KB') {
            $num = (float)str_replace('KB', '', $v);
            $this->maxSizeBytes = (int)($num * 1024);
        } elseif (substr($v, -2) === 'MB') {
            $num = (float)str_replace('MB', '', $v);
            $this->maxSizeBytes = (int)($num * 1024 * 1024);
        } elseif (substr($v, -2) === 'GB') {
            $num = (float)str_replace('GB', '', $v);
            $this->maxSizeBytes = (int)($num * 1024 * 1024 * 1024);
        } elseif (substr($v, -1) === 'B') {
            $num = (float)str_replace('B', '', $v);
            $this->maxSizeBytes = (int)$num;
        } else {
            $this->maxSizeBytes = (int)$value; // fallback: bytes
        }
        return $this;
    }

    // ->ext(['pdf','docx']) or ->ext('pdf, docx')
    public function ext($extensions): self
    {
        if (is_string($extensions)) {
            $extensions = preg_split('/[,\s]+/', $extensions, -1, PREG_SPLIT_NO_EMPTY);
        }
        if (is_array($extensions)) {
            $norm = [];
            foreach ($extensions as $e) {
                $e = strtolower(ltrim(trim((string)$e), '.'));
                if ($e !== '') $norm[] = $e;
            }
            $this->allowedExt = array_values(array_unique($norm));
        }
        return $this;
    }

    public function overwrite(bool $yes = true): self
    {
        $this->overwrite = $yes;
        return $this;
    }

    /*** HELPERS (always 4-parameter LtResponse::json) ***/
    protected function ok(string $message, array $data = [], string $code = self::RC_FILE_UPLOADED)
    {
        return LtResponse::json($message, $code, '200', $data);
    }

    protected function fail(string $message, array $data = [], string $code = self::RC_INVALID_UPLOAD)
    {
        // failure => responseCategory "100"
        return LtResponse::json($message, $code, '100', $data);
    }

    protected function sanitizeName(string $name): string
    {
        if ($name === '') return $name;
        if (strpos($name, '..') !== false || $name[0] === '/' || $name[0] === '\\') {
            $name = basename($name);
        }
        $name = preg_replace('/[^A-Za-z0-9._-]/', '_', $name);
        return preg_replace('/_+/', '_', $name);
    }

    protected function ensureBaseExists(): bool
    {
        if (is_dir($this->basePath)){
            return true;
        }else if (is_file($this->basePath)){
            return true;
        }else{
            return false;
        }
        
    }

    protected function resolveFileInput($file)
    {
        // Accept a string key or a full $_FILES[...] array
        if (is_string($file)) {
            return $_FILES[$file] ?? null;
        }
        if (is_array($file) && isset($file['tmp_name'])) {
            return $file;
        }
        return null;
    }

    /*** OPERATIONS ***/

    // Upload into base path (base path MUST exist). $file can be a key ('filedocument') or the $_FILES[...] array.
    public function uploadFile($file)
    {
        if (!$this->ensureBaseExists()) {
            return $this->fail('Target directory does not exist.', ['targetDir' => $this->basePath], self::RC_BASE_PATH_NOT_FOUND);
        }

        $input = $this->resolveFileInput($file);
        if (!$input || ($input['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return $this->fail('No valid file uploaded.', [], self::RC_INVALID_UPLOAD);
        }

        if ($this->maxSizeBytes !== null && (int)$input['size'] > $this->maxSizeBytes) {
            return $this->fail('File exceeds maximum size limit.', [
                'maxSizeBytes' => $this->maxSizeBytes,
                'actualSize'   => (int)$input['size']
            ], self::RC_SIZE_EXCEEDED);
        }

        $orig = $input['name'] ?? 'upload.bin';
        $safe = $this->sanitizeName($orig);
        $ext  = strtolower(pathinfo($safe, PATHINFO_EXTENSION) ?: '');

        if (!empty($this->allowedExt) && ($ext === '' || !in_array($ext, $this->allowedExt, true))) {
            return $this->fail('Extension not allowed.', [
                'allowed' => $this->allowedExt,
                'found'   => $ext ?: '(none)'
            ], self::RC_EXT_NOT_ALLOWED);
        }

        if (!is_uploaded_file($input['tmp_name'])) {
            return $this->fail('Security check failed for uploaded file.', [], self::RC_SECURITY_CHECK_FAIL);
        }

        $dest = $this->basePath . $safe;
        // if (file_exists($dest) && !$this->overwrite) {
        //     $info = pathinfo($safe);
        //     $base = $info['filename'] ?? 'file';
        //     $dot  = isset($info['extension']) && $info['extension'] !== '' ? '.' . $info['extension'] : '';
        //     $dest = $this->basePath . $base . '_' . uniqid() . $dot;
        // }
        if (file_exists($dest)) {
            
            if($this->overwrite){
                // if overwite is allowed, remove the old one for clean replacement
                unlink($dest);
            }else{
                
                $info = pathinfo($safe);
                $base = $info['filename'] ?? 'file';
                $dot  = isset($info['extension']) && $info['extension'] !== '' ? '.' . $info['extension'] : '';
                $dest = $this->basePath . $base . '_' . uniqid() . $dot;
            }
            
        }

        if (@move_uploaded_file($input['tmp_name'], $dest)) {
            return $this->ok('File uploaded successfully.', [
                'file_path' => $dest,
                'file_name' => basename($dest),
                'ext'       => $ext,
                'size'      => (int)$input['size']
            ], self::RC_FILE_UPLOADED);
        }

        return $this->fail('Failed to move uploaded file.', [], self::RC_MOVE_FAILED);
    }

    // Create a file under base path (optionally with initial content)
    public function createFile(string $fileName, string $initialContent = '')
    {
        if (!$this->ensureBaseExists()) {
            return $this->fail('Base path does not exist.', ['path' => $this->basePath], self::RC_BASE_PATH_NOT_FOUND);
        }

        $fileName = $this->sanitizeName($fileName);
        $dest = $this->basePath . $fileName;

        if (file_exists($dest) && !$this->overwrite) {
            return $this->fail('File already exists.', ['file' => $dest], self::RC_FILE_EXISTS);
        }

        if ($this->maxSizeBytes !== null && strlen($initialContent) > $this->maxSizeBytes) {
            return $this->fail('Initial content exceeds maximum size.', [
                'maxSizeBytes' => $this->maxSizeBytes,
                'contentSize'  => strlen($initialContent)
            ], self::RC_SIZE_EXCEEDED);
        }

        // Strict: do not auto-create parent folder
        $dir = dirname($dest);
        if (!is_dir($dir)) {
            return $this->fail('Parent directory does not exist.', ['dir' => $dir], self::RC_PARENT_DIR_NOT_FOUND);
        }

        if (@file_put_contents($dest, $initialContent) !== false) {
            return $this->ok('File created.', [
                'file' => $dest,
                'size' => strlen($initialContent)
            ], self::RC_FILE_CREATED);
        }

        return $this->fail('Failed to create file.', ['file' => $dest], self::RC_MOVE_FAILED);
    }

    // Write (overwrite) an existing file
    public function writeFile(string $fileName, string $content)
    {
        if (!$this->ensureBaseExists()) {
            return $this->fail('Base path does not exist.', ['path' => $this->basePath], self::RC_BASE_PATH_NOT_FOUND);
        }

        $fileName = $this->sanitizeName($fileName);
        $dest = $this->basePath . $fileName;

        if (!file_exists($dest)) {
            return $this->fail('File not found.', ['file' => $dest], self::RC_FILE_NOT_FOUND);
        }

        if ($this->maxSizeBytes !== null && strlen($content) > $this->maxSizeBytes) {
            return $this->fail('Content exceeds maximum size.', [
                'maxSizeBytes' => $this->maxSizeBytes,
                'contentSize'  => strlen($content)
            ], self::RC_SIZE_EXCEEDED);
        }

        if (@file_put_contents($dest, $content) !== false) {
            return $this->ok('File written.', [
                'file' => $dest,
                'size' => strlen($content)
            ], self::RC_FILE_WRITTEN);
        }

        return $this->fail('Failed to write file.', ['file' => $dest], self::RC_MOVE_FAILED);
    }

    // Delete a file under base path
    public function deleteFile(string $fileName)
    {
        if (!$this->ensureBaseExists()) {
            return $this->fail('Base path does not exist.', ['path' => $this->basePath], self::RC_BASE_PATH_NOT_FOUND);
        }

        $fileName = $this->sanitizeName($fileName);
        $target = $this->basePath . $fileName;

        if (!file_exists($target)) {
            return $this->fail('File not found.', ['file' => $target], self::RC_FILE_NOT_FOUND);
        }

        if (@unlink($target)) {
            return $this->ok('File deleted.', ['file' => $target], self::RC_FILE_DELETED);
        }
        return $this->fail('Failed to delete file.', ['file' => $target], self::RC_MOVE_FAILED);
    }

    // Create subfolder under base path
    public function createFolder(string $folderName) // kept your casing
    {
        if (!$this->ensureBaseExists()) {
            return $this->fail('Base path does not exist.', ['path' => $this->basePath], self::RC_BASE_PATH_NOT_FOUND);
        }

        $folderName = $this->sanitizeName($folderName);
        $dir = rtrim($this->basePath . $folderName, '/\\');

        if (is_dir($dir)) {
            return $this->ok('Directory already exists.', ['dir' => $dir], self::RC_DIR_EXISTS);
        }

        if (@mkdir($dir, 0777, true)) {
            return $this->ok('Directory created.', ['dir' => $dir], self::RC_DIR_CREATED);
        }
        return $this->fail('Failed to create directory.', ['dir' => $dir], self::RC_DIR_CREATE_FAILED);
    }

    // Delete subfolder under base path (non-recursive by default)
    public function deleteFolder(string $folderName, bool $recursive = false) // kept yours casing
    {
        if (!$this->ensureBaseExists()) {
            return $this->fail('Base path does not exist.', ['path' => $this->basePath], self::RC_BASE_PATH_NOT_FOUND);
        }

        $folderName = $this->sanitizeName($folderName);
        $dir = rtrim($this->basePath . $folderName, '/\\');

        if (!is_dir($dir)) {
            return $this->fail('Directory not found.', ['dir' => $dir], self::RC_DIR_NOT_FOUND);
        }

       // if ($recursive) {
            $items = scandir($dir);
            if ($items !== false) {
                foreach ($items as $item) {
                    if ($item === '.' || $item === '..') continue;
                    $p = $dir . DIRECTORY_SEPARATOR . $item;
                    if (is_dir($p)) {
                        // Recurse
                        $this->path($dir . DIRECTORY_SEPARATOR)->deleteFolder($item, true);
                    } else {
                        @unlink($p);
                    }
                }
            }
       // }

        if (@rmdir($dir)) {
            return $this->ok('Directory deleted.', ['dir' => $dir], self::RC_DIR_DELETED);
        }
        return $this->fail('Failed to delete directory. Is it empty?', ['dir' => $dir], self::RC_DIR_DELETE_FAILED);
    }
    
    public function download(string $fileName){
        
        if (!$this->ensureBaseExists()) {
            return $this->fail('Base path does not exist.', ['path' => $this->basePath], self::RC_BASE_PATH_NOT_FOUND);
        }
        
        if(!class_exists('ZipArchive')){
            return $this->fail('ZipArchive is not Activated on your PhP Configuration. You need to Activate ZipArchive! on your PhP Configuration!!!  RETRY', 1716, RC_ZIP_CREATION_FAILED);
        }

        
        $fileName = $this->sanitizeName($fileName);
        $target = $this->basePath . $fileName;
    
        if (is_dir($target)) {
    
            $zipName = $fileName . '.zip';
            $zipPath = $this->basePath . $zipName;
    
            $zip = new ZipArchive;
    
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    
                // Add content to zip
                $this->createZip($target, $zip, $target);
    
                $zip->close();
            } else {
                return $this->fail("Failed to create zip file.");
            }
    
            // Send ZIP to browser
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . basename($zipName) . '"');
            header('Content-Length: ' . filesize($zipPath));
    
            readfile($zipPath);
            unlink($zipPath);
            return $this->ok('Folder downloaded successfully.', ['path' => $this->basePath], self::RC_BASE_PATH_NOT_FOUND);
        }
        if (is_file($target)) {
    
            header('Content-Type: ' . mime_content_type($target));
            header('Content-Disposition: attachment; filename="' . basename($target) . '"');
            header('Content-Length: ' . filesize($target));
            header('Cache-Control: must-revalidate');
    
            readfile($target);
            exit();
            return $this->ok('File downloaded successfully.', ['path' => $this->basePath], self::RC_BASE_PATH_NOT_FOUND);
        }
    
        // If neither file nor directory
        return $this->fail('Target does not exist.', ['target' => $target], self::RC_FILE_NOT_FOUND);
}

    
    public function createZip($folder, &$zipFile, $target){
        $exclusiveLength = strlen($target);
        $handle = opendir($folder);
        if (!$handle) {
            throw new Exception("Cannot open directory: $folder");
        }
    
        $baseFolder = basename($target);
    
        while (($f = readdir($handle)) !== false) {
            if ($f == '.' || $f == '..') continue;
    
            $filePath = $folder . '/' . $f;
            $localPath = substr($filePath, $exclusiveLength);
            $localPath = ltrim($localPath, '/\\');
    
            // Wrap everything inside root folder
            $localPath = $baseFolder . '/' . $localPath;
    
            if (is_file($filePath)) {
                $zipFile->addFile($filePath, $localPath);
            } elseif (is_dir($filePath)) {
                $zipFile->addEmptyDir($localPath);
                $this->createZip($filePath, $zipFile, $target);
            }
        }
    
        closedir($handle);
    }

    
    public function copy($dst){
        if (!$this->ensureBaseExists()) {
            return $this->fail('Base path does not exist.', ['path' => $this->basePath], self::RC_BASE_PATH_NOT_FOUND);
        }
        
        
        $dst = $dst . basename($this->basePath);

         if(is_file($this->basePath)){
             
            if (copy($this->basePath, $dst)) {
                return $this->ok('File copied successfully.', ['path' => $this->basePath], self::RC_BASE_PATH_NOT_FOUND);
            
            } else {
                return $this->fail('Failed to copy file.', ['path' => $this->basePath], self::RC_BASE_PATH_NOT_FOUND);
         
            }

         }    
        
         if(is_dir($this->basePath)){
            if ($this->copyFolder($this->basePath, $dst)) {
                return $this->ok('folder copied successfully.', ['path' => $this->basePath], self::RC_BASE_PATH_NOT_FOUND);
            
            } else {
                return $this->fail('Failed to copy folder.', ['path' => $this->basePath], self::RC_BASE_PATH_NOT_FOUND);
         
            }
         }
        
    }
    
   public function copyFolder($src, $dst) {
        $dir = opendir($src);
        if (!is_dir($dst)) {
            if (!mkdir($dst, 0755, true)) {
                return false;
            }
        }
    
        while (false !== ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                $srcPath = "$src/$file";
                $dstPath = "$dst/$file";
                if (is_dir($srcPath)) {
                    if (!$this->copyFolder($srcPath, $dstPath)) {
                        return false;
                    }
                } else {
                    if (!copy($srcPath, $dstPath)) {
                        return false;
                    }
                }
            }
        }
        closedir($dir);
        return true;
    }
    
    public function move($dst){
        if (!$this->ensureBaseExists()) {
            return $this->fail('Base path does not exist.', ['path' => $this->basePath], self::RC_BASE_PATH_NOT_FOUND);
        }
        
        
        $dst = $dst . basename($this->basePath);
             
        if (rename($this->basePath, $dst)) {
            return $this->ok('File Moved successfully.', ['path' => $this->basePath], self::RC_BASE_PATH_NOT_FOUND);
        
        } else {
            return $this->fail('Failed to move file.', ['path' => $this->basePath], self::RC_BASE_PATH_NOT_FOUND);
     
        }

        
    }
    
   public function moveFolder(string $src, string $dest, bool $overwrite = true)
{
    // Normalize paths
    $src  = rtrim($src, "/\\") . DIRECTORY_SEPARATOR;
    $dest = rtrim($dest, "/\\") . DIRECTORY_SEPARATOR;
    // $this->deleteFolder2($src);
    // Validate
     //$this->unlink($src);
    if (!is_dir($src)) return false;
    if (!is_dir($dest) && !mkdir($dest, 0777, true)) return false;

    $dir = new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::SELF_FIRST);

    foreach ($files as $file) {
        $targetPath = $dest . str_replace($src, '', $file->getPathname());

        if ($file->isDir()) {
            if (!is_dir($targetPath)) mkdir($targetPath, 0777, true);
        } else {
            if ($overwrite || !file_exists($targetPath)) {
                copy($file->getPathname(), $targetPath);
            }
        }
    }

    // Optional: remove the source folder after move
    $this->deleteFolder2($src);

    return true;
}

public function deleteFolder2(string $dir): void
{
    if (!is_dir($dir)) return;
    $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $file) {
        $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
    }
    rmdir($dir);
}

     
    public function runUnlink($folderPath, $deleteMainFolder="yes") {
       if (!is_dir($folderPath)) {
        return false;
        }


        $files = array_diff(scandir($folderPath), ['.', '..']);
    
        foreach ($files as $file) {
            $fullPath = $folderPath . DIRECTORY_SEPARATOR . $file;
    
            if (is_dir($fullPath)) {
                $this->runUnlink($fullPath);   // recursive delete
                rmdir($fullPath);
            } else {
                unlink($fullPath);         // delete file
            }
        }
    
       
        if ($deleteMainFolder==="yes"){
            return rmdir($folderPath); // delete the now-empty folder
        }
         return true;
        
        
     }
    
    public function unlink($fileName, $deleteMainFolder="yes") {
        if (!$this->ensureBaseExists()) {
            return $this->fail('Base path does not exist.', ['path' => $this->basePath], self::RC_BASE_PATH_NOT_FOUND);
        }
        
           $fileName = $this->sanitizeName($fileName);
           $folderPath = $this->basePath . $fileName;
            if (!is_dir($folderPath)) {
            unlink($folderPath);
            }else{
              $this->runUnlink($folderPath, $deleteMainFolder);
            }
            return true;
        
    }

    
    
}