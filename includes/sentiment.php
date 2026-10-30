<?php
declare(strict_types=1);

function classify_feedback_sentiment(string $text, int $rating): string
{
    $positive = ['amazing', 'awesome', 'excellent', 'enjoy', 'enjoyed', 'friendly', 'good', 'great', 'helpful', 'love', 'loved', 'perfect', 'smooth', 'wonderful'];
    $negative = ['awful', 'bad', 'boring', 'confusing', 'delay', 'delayed', 'difficult', 'disappoint', 'late', 'poor', 'problem', 'rude', 'slow', 'terrible', 'wait'];
    $normalized = strtolower($text);
    $positiveScore = 0;
    $negativeScore = 0;

    foreach ($positive as $word) {
        $positiveScore += preg_match('/\b' . preg_quote($word, '/') . '\b/', $normalized) ? 1 : 0;
    }
    foreach ($negative as $word) {
        $negativeScore += preg_match('/\b' . preg_quote($word, '/') . '\b/', $normalized) ? 1 : 0;
    }

    if ($rating >= 4) {
        $positiveScore++;
    } elseif ($rating <= 2) {
        $negativeScore++;
    }

    if ($positiveScore > 0 && $negativeScore > 0) {
        return 'Mixed';
    }
    if ($positiveScore > $negativeScore) {
        return 'Positive';
    }
    if ($negativeScore > $positiveScore) {
        return 'Negative';
    }
    return 'Neutral';
}
