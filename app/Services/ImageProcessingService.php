<?php

    namespace App\Services;

    use Tinify\Tinify;
    use Tinify\Source;
    use Exception;
    use Illuminate\Http\UploadedFile;

    /**
     * Class ImageProcessingService
     *
     * Service for image processing using the Tinify API.
     */
    class ImageProcessingService
    {
        /**
         * ImageProcessingService constructor.
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
            $photoPath = $photo->store('photos', 'public');
            $storagePath = storage_path("app/public/$photoPath");

            $this->cropAndOptimizeImage($storagePath, $storagePath);

            return $photoPath;
        }
    }
