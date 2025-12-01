<?php
// app/Services/FaceRecognitionService.php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FaceRecognitionService
{
    /**
     * Verify face against database
     * 
     * @param array $embeddings - Face embeddings from IoT device
     * @param float $threshold - Similarity threshold (0-1)
     * @return array
     */
    public function verifyFace(array $embeddings, float $threshold = 0.6): array
    {
        $students = Student::where('is_active', true)
            ->whereNotNull('face_embeddings')
            ->get();

        if ($students->isEmpty()) {
            return [
                'matched' => false,
                'message' => 'No registered students found',
            ];
        }

        $bestMatch = null;
        $bestSimilarity = 0;

        foreach ($students as $student) {
            $storedEmbeddings = $student->face_embeddings;
            
            if (empty($storedEmbeddings)) {
                continue;
            }

            // Calculate cosine similarity
            $similarity = $this->cosineSimilarity($embeddings, $storedEmbeddings);
            
            if ($similarity > $bestSimilarity) {
                $bestSimilarity = $similarity;
                $bestMatch = $student;
            }
        }

        if ($bestMatch && $bestSimilarity >= $threshold) {
            Log::info('Face recognized', [
                'student_id' => $bestMatch->id,
                'student_name' => $bestMatch->name,
                'confidence' => $bestSimilarity * 100,
            ]);

            return [
                'matched' => true,
                'student' => $bestMatch,
                'confidence' => round($bestSimilarity * 100, 2),
                'message' => 'Face recognized successfully',
            ];
        }

        Log::warning('Face not recognized', [
            'best_similarity' => $bestSimilarity * 100,
            'threshold' => $threshold * 100,
        ]);

        return [
            'matched' => false,
            'confidence' => $bestMatch ? round($bestSimilarity * 100, 2) : 0,
            'message' => 'Face not recognized or confidence too low',
            'best_match' => $bestMatch ? [
                'student_name' => $bestMatch->name,
                'similarity' => round($bestSimilarity * 100, 2),
            ] : null,
        ];
    }

    /**
     * Register new face embeddings for a student
     */
    public function registerFace(int $studentId, array $embeddings, string $photoBase64): array
    {
        try {
            $student = Student::findOrFail($studentId);

            // Save photo
            $photoPath = $this->saveBase64Image($photoBase64, 'faces');

            // Update student
            $student->update([
                'face_image' => $photoPath,
                'face_embeddings' => $embeddings,
            ]);

            Log::info('Face registered', [
                'student_id' => $studentId,
                'student_name' => $student->name,
            ]);

            return [
                'success' => true,
                'message' => 'Face registered successfully',
                'student' => $student,
            ];

        } catch (\Exception $e) {
            Log::error('Face registration failed', [
                'student_id' => $studentId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Calculate cosine similarity between two vectors
     */
    public function cosineSimilarity(array $a, array $b): float
    {
        if (count($a) !== count($b)) {
            throw new \InvalidArgumentException('Vectors must have the same length');
        }

        $dotProduct = 0;
        $magnitudeA = 0;
        $magnitudeB = 0;

        for ($i = 0; $i < count($a); $i++) {
            $dotProduct += $a[$i] * $b[$i];
            $magnitudeA += $a[$i] * $a[$i];
            $magnitudeB += $b[$i] * $b[$i];
        }

        $magnitudeA = sqrt($magnitudeA);
        $magnitudeB = sqrt($magnitudeB);

        if ($magnitudeA == 0 || $magnitudeB == 0) {
            return 0;
        }

        return $dotProduct / ($magnitudeA * $magnitudeB);
    }

    /**
     * Calculate euclidean distance (alternative to cosine similarity)
     */
    public function euclideanDistance(array $a, array $b): float
    {
        if (count($a) !== count($b)) {
            throw new \InvalidArgumentException('Vectors must have the same length');
        }

        $sum = 0;
        for ($i = 0; $i < count($a); $i++) {
            $sum += pow($a[$i] - $b[$i], 2);
        }

        return sqrt($sum);
    }

    /**
     * Validate face embeddings
     */
    public function validateEmbeddings(array $embeddings): bool
    {
        // Check if array is not empty
        if (empty($embeddings)) {
            return false;
        }

        // Check if all values are numeric
        foreach ($embeddings as $value) {
            if (!is_numeric($value)) {
                return false;
            }
        }

        // Common face embedding dimensions: 128, 512
        $validDimensions = [128, 512];
        if (!in_array(count($embeddings), $validDimensions)) {
            Log::warning('Unusual embedding dimension', [
                'dimension' => count($embeddings),
            ]);
        }

        return true;
    }

    /**
     * Save base64 image to storage
     */
    protected function saveBase64Image(string $base64String, string $directory = 'images'): string
    {
        // Remove data URI prefix if exists
        if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $type)) {
            $base64String = substr($base64String, strpos($base64String, ',') + 1);
            $type = strtolower($type[1]);
        }

        $image = base64_decode($base64String);
        
        if ($image === false) {
            throw new \InvalidArgumentException('Invalid base64 image');
        }

        $filename = uniqid() . '_' . time() . '.jpg';
        $path = $directory . '/' . $filename;

        Storage::disk('public')->put($path, $image);

        return $path;
    }

    /**
     * Get face recognition statistics
     */
    public function getStatistics(): array
    {
        $totalStudents = Student::where('is_active', true)->count();
        $withFaceData = Student::where('is_active', true)
            ->whereNotNull('face_embeddings')
            ->count();

        $percentage = $totalStudents > 0 
            ? round(($withFaceData / $totalStudents) * 100, 2) 
            : 0;

        return [
            'total_students' => $totalStudents,
            'registered_faces' => $withFaceData,
            'unregistered' => $totalStudents - $withFaceData,
            'registration_percentage' => $percentage,
        ];
    }

    /**
     * Bulk verify faces (for testing)
     */
    public function bulkVerify(array $facesData): array
    {
        $results = [];

        foreach ($facesData as $data) {
            $result = $this->verifyFace(
                $data['embeddings'],
                $data['threshold'] ?? 0.6
            );

            $results[] = array_merge($result, [
                'input_id' => $data['id'] ?? null,
            ]);
        }

        return $results;
    }

    /**
     * Recommend threshold based on verification attempts
     */
    public function recommendThreshold(array $testResults): float
    {
        // Analyze test results to recommend optimal threshold
        $truePositives = collect($testResults)->where('matched', true)->count();
        $falsePositives = collect($testResults)->where('matched', false)->count();

        // Simple recommendation logic
        if ($truePositives > $falsePositives * 2) {
            return 0.7; // Higher threshold for better accuracy
        } elseif ($truePositives > $falsePositives) {
            return 0.6; // Balanced threshold
        } else {
            return 0.5; // Lower threshold for better recall
        }
    }
}
