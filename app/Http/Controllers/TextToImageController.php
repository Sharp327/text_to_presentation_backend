<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use OpenAI;

class TextToImageController extends Controller
{
    public function index()
    {
        return view('text-to-image');
    }

    public function generatePresentation(Request $request)
    {

        $request->validate([
            'content' => 'required|string',
        ]);

        $apiKey = env('OPENAI_API_KEY');
        $openai = OpenAI::client($apiKey);

        // Call OpenAI API to divide content into 6 related parts
        $response = $openai->chat()->create([
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an assistant that divides user-provided text into six distinct and related parts for a presentation diagram.',
                ],
                [
                    'role' => 'user',
                    'content' => "Divide the following content into six related parts for a presentation diagram. Response should include:
                                1. A main title for the entire content.
                                2. Maximum six distinct and related parts, each with:
                                - A descriptive subtitle (DescTitle): Maximum 5 words.
                                - A detailed description (Description) explaining the topic of the part: Maximum 10 words.

                                For reference, check this example JSON structure:

                                {
                                \"Title\": \"Main title for the entire content\",
                                \"content\": [
                                    {
                                    \"DescTitle\": \"Subtitle for part 1\",
                                    \"Description\": \"Detailed description for part 1.\"
                                    },
                                    {
                                    \"DescTitle\": \"Subtitle for part 2\",
                                    \"Description\": \"Detailed description for part 2.\"
                                    },
                                    {
                                    \"DescTitle\": \"Subtitle for part 3\",
                                    \"Description\": \"Detailed description for part 3.\"
                                    },
                                    {
                                    \"DescTitle\": \"Subtitle for part 4\",
                                    \"Description\": \"Detailed description for part 4.\"
                                    },
                                    {
                                    \"DescTitle\": \"Subtitle for part 5\",
                                    \"Description\": \"Detailed description for part 5.\"
                                    },
                                    {
                                    \"DescTitle\": \"Subtitle for part 6\",
                                    \"Description\": \"Detailed description for part 6.\"
                                    }
                                ]
                                }

                                Use the following content to generate the output:\n\n" . $request->input('content'),
                ],
            ],
        ]);

        $data = json_decode($response['choices'][0]['message']['content'], true);

        // Return the response as JSON
        return response()->json(['data' => $data]);
    }

    private function replaceSvgParts(string $svgPath, array $parts)
    {
        $svgContent = file_get_contents($svgPath);

        // Replace parts in the SVG based on predefined IDs
        foreach ($parts as $index => $part) {
            $id = "part" . ($index + 1);
            $svgContent = preg_replace(
                '/<text id="' . $id . '"[^>]*>.*?<\/text>/',
                '<text id="' . $id . '">' . htmlspecialchars($part) . '</text>',
                $svgContent
            );
        }

        return $svgContent;
    }
}
