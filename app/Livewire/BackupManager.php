<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\Process\Process;

class BackupManager extends Component
{
    use WithFileUploads;

    public $backups = [];
    public $isGenerating = false;
    public $message = '';
    public $messageType = '';

    public function mount()
    {
        if (!File::exists(storage_path('app/backup'))) {
            File::makeDirectory(storage_path('app/backup'), 0755, true);
        }
        $this->refreshBackupsList();
    }

    public function refreshBackupsList()
    {
        if (!File::exists(storage_path('app/backup'))) {
            $this->backups = [];
            return;
        }

        $this->backups = collect(File::files(storage_path('app/backup')))
            ->filter(function ($file) {
                return str_ends_with($file->getFilename(), '.sql');
            })
            ->map(function ($file) {
                return [
                    'name' => $file->getFilename(),
                    'size' => $file->getSize(),
                    'last_modified' => Carbon::createFromTimestamp($file->getMTime())
                        ->format('Y-m-d H:i:s'),
                    'path' => 'backup/' . $file->getFilename()
                ];
            })
            ->sortByDesc('last_modified')
            ->values()
            ->toArray();
    }

    public function createBackup()
    {
        try {
            set_time_limit(300);
            
            $this->isGenerating = true;
            $this->message = 'Generating backup...';
            $this->messageType = 'info';
    
            $filename = 'backup_' . date('Y-m-d_His') . '.sql';
            $outputPath = storage_path('app/backup/' . $filename);
    
            // Get all tables
            $tables = \DB::select('SHOW TABLES');
            $output = '';
    
            foreach ($tables as $table) {
                $tableName = reset($table);
                
                // Get Create Table Syntax
                $createTable = \DB::select("SHOW CREATE TABLE `$tableName`");
                $output .= "\n\n" . $createTable[0]->{'Create Table'} . ";\n\n";
    
                // Get Table Content
                $rows = \DB::table($tableName)->get();
                foreach ($rows as $row) {
                    $rowData = (array) $row;
                    $values = array_map(function ($value) {
                        return is_null($value) ? 'NULL' : \DB::getPdo()->quote($value);
                    }, $rowData);
                    
                    $output .= "INSERT INTO `$tableName` VALUES (" . implode(', ', $values) . ");\n";
                }
            }
    
            if (File::put($outputPath, $output)) {
                $this->message = 'Backup created successfully!';
                $this->messageType = 'success';
                $this->refreshBackupsList();
            } else {
                throw new \Exception('Failed to write backup file');
            }
    
        } catch (\Exception $e) {
            $this->message = 'Error creating backup: ' . $e->getMessage();
            $this->messageType = 'error';
            \Log::error('Backup creation failed: ' . $e->getMessage());
        } finally {
            $this->isGenerating = false;
        }
    }

    public function restoreBackup($path)
    {
        try {
            // Set unlimited execution time
            set_time_limit(0);
            ini_set('memory_limit', '512M');
            
            $this->message = 'Restoring backup...';
            $this->messageType = 'info';
    
            $fullPath = storage_path('app/' . $path);
            if (!File::exists($fullPath)) {
                throw new \Exception('Backup file not found');
            }
    
            // Read the SQL file in chunks
            $handle = fopen($fullPath, 'r');
            if ($handle) {
                try {
                    // Disable foreign key checks
                    \DB::statement('SET FOREIGN_KEY_CHECKS=0');
    
                    // Drop all existing tables first
                    $tables = \DB::select('SHOW TABLES');
                    foreach ($tables as $table) {
                        $tableName = reset($table);
                        \DB::statement("DROP TABLE IF EXISTS `$tableName`");
                    }
    
                    $query = '';
                    while (!feof($handle)) {
                        $chunk = fgets($handle);
                        $query .= $chunk;
    
                        // Execute when we have a complete query (ends with semicolon)
                        if (str_ends_with(trim($query), ';')) {
                            try {
                                if (!empty(trim($query))) {
                                    \DB::unprepared($query);
                                }
                            } catch (\Exception $e) {
                                \Log::warning("Query failed: " . substr($query, 0, 100) . "... Error: " . $e->getMessage());
                                throw $e; // Re-throw to handle in outer catch
                            }
                            $query = '';
                        }
                    }
    
                    // Re-enable foreign key checks
                    \DB::statement('SET FOREIGN_KEY_CHECKS=1');
                    
                    $this->message = 'Backup restored successfully!';
                    $this->messageType = 'success';
    
                } catch (\Exception $e) {
                    // Re-enable foreign key checks even if restore fails
                    \DB::statement('SET FOREIGN_KEY_CHECKS=1');
                    throw new \Exception('Failed to restore backup: ' . $e->getMessage());
                } finally {
                    fclose($handle);
                }
            } else {
                throw new \Exception('Could not open backup file');
            }
    
        } catch (\Exception $e) {
            $this->message = 'Error restoring backup: ' . $e->getMessage();
            $this->messageType = 'error';
            \Log::error('Backup restoration failed: ' . $e->getMessage());
        }
    }

    public function downloadBackup($path)
    {
        $fullPath = storage_path('app/' . $path);
        if (File::exists($fullPath)) {
            return response()->download($fullPath);
        }

        $this->message = 'Backup file not found';
        $this->messageType = 'error';
    }

    public function deleteBackup($path)
    {
        try {
            $fullPath = storage_path('app/' . $path);
            if (File::exists($fullPath)) {
                File::delete($fullPath);
                $this->message = 'Backup deleted successfully!';
                $this->messageType = 'success';
            } else {
                $this->message = 'Backup file not found';
                $this->messageType = 'error';
            }
            $this->refreshBackupsList();
        } catch (\Exception $e) {
            $this->message = 'Error deleting backup: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function render()
    {
        return view('livewire.backup-manager')->layout('layouts.app');
    }
}
