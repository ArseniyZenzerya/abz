<?php

    namespace App\Services;

    use Tinify\Tinify;
    use Tinify\Source;
    use Exception;
    use Illuminate\Http\UploadedFile;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\Storage;

    /**
     * Class ImageProcessingService
     *
     * Service for image processing using the Tinify API.
     * This service handles image uploading, optimization, cropping, and storage.
     */
    class ImageProcessingService
    {
        /**
         * ImageProcessingService constructor.
         *
         * Initializes the service and sets the Tinify API key.
         *
         * @throws Exception If the Tinify API key is not set in the .env file.
         */
        public function __construct()
        {
            $this->setTinifyApiKey();
        }

        /**
         * Sets the Tinify API key from the environment variables.
         *
         * @throws Exception If the API key is missing.
         */
        private function setTinifyApiKey(): void
        {
            $apiKey = env('TINIFY_API_KEY');
            if (!$apiKey) {
                throw new Exception('Tinify API key is missing in .env file.');
            }
            Tinify::setKey($apiKey);
        }

        /**
         * Crops and optimizes an image to the specified dimensions.
         *
         * @param string $inputPath The path to the input image.
         * @param string $outputPath The path to save the processed image.
         * @param int $width The width of the resulting image (default: 70px).
         * @param int $height The height of the resulting image (default: 70px).
         *
         * @throws Exception If an error occurs during image processing.
         */
        public function cropAndOptimizeImage(string $inputPath, string $outputPath, int $width = 70, int $height = 70): void
        {
            try {
                $source = Source::fromFile($inputPath);
                $resized = $source->resize([
                    "method" => "cover",
                    "width" => $width,
                    "height" => $height
                ]);
                $resized->toFile($outputPath);
            } catch (Exception $e) {
                Log::error("Tinify error: " . $e->getMessage());
                throw new Exception("Error during image processing: " . $e->getMessage());
            }
        }

        /**
         * Stores and optimizes a user-uploaded photo.
         *
         * @param UploadedFile $photo The uploaded file instance.
         *
         * @return string|null The path to the stored and optimized photo or null if failed.
         */
        public function storeAndOptimizePhoto(UploadedFile $photo): ?string
        {
            $storagePath = $this->storePhotoInStorage($photo);

            $this->optimizePhoto($storagePath);

            $publicPath = $this->movePhotoToPublic($storagePath);

            return $publicPath;
        }

        /**
         * Saves the uploaded photo to the storage directory.
         *
         * @param UploadedFile $photo The uploaded file instance.
         * @return string The full path to the stored photo.
         * @throws Exception If the file cannot be saved.
         */
        private function storePhotoInStorage(UploadedFile $photo): string
        {
            $fileName = 'photos/' . uniqid() . '.' . $photo->getClientOriginalExtension();
            $photo->storeAs('public/photos', basename($fileName));

            return storage_path('app/public/photos/' . basename($fileName));
        }

        /**
         * Optimizes and crops the photo.
         *
         * @param string $filePath The full path to the photo.
         * @throws Exception If optimization fails.
         */
        private function optimizePhoto(string $filePath): void
        {
            try {
                $this->cropAndOptimizeImage($filePath, $filePath);
            } catch (Exception $e) {
                Storage::delete('public/photos/' . basename($filePath));
                Log::error("Failed to optimize image: " . $e->getMessage());
                throw new Exception("Failed to optimize the image.");
            }
        }

        /**
         * Moves the photo from storage to the public directory.
         *
         * @param string $storagePath The full path to the photo in storage.
         * @return string The relative path to the photo in the public directory.
         * @throws Exception If the file cannot be moved.
         */
        private function movePhotoToPublic(string $storagePath): string
        {
            $publicPath = public_path('photos');
            $this->ensureDirectoryExists($publicPath);

            $fileName = basename($storagePath);
            $fullPublicPath = $publicPath . '/' . $fileName;

            if (!rename($storagePath, $fullPublicPath)) {
                Log::error("Failed to move file to: " . $fullPublicPath);
                throw new Exception("Failed to move the file to public directory.");
            }

            return 'photos/' . $fileName;
        }

        /**
         * Ensures that the specified directory exists.
         *
         * @param string $directory The directory path.
         * @throws Exception If the directory cannot be created.
         */
        private function ensureDirectoryExists(string $directory): void
        {
            if (!file_exists($directory)) {
                if (!mkdir($directory, 0777, true)) {
                    Log::error("Failed to create directory: " . $directory);
                    throw new Exception("Failed to create directory for photos.");
                }
            }
        }
    }
