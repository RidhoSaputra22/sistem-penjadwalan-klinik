<?php

namespace App\Services\Nlp;

use Illuminate\Support\Str;

class ServiceRecommender
{
    /**
     * Stopwords dasar Bahasa Indonesia + kata umum.
     * Bisa dioverride via config('chatbot.stopwords').
     *
     * @var array<int, string>
     */
    private const DEFAULT_STOPWORDS = [
        'yang', 'dan', 'di', 'ke', 'dari', 'pada', 'untuk', 'dengan', 'atau', 'juga', 'agar', 'supaya',
        'saya', 'aku', 'kami', 'kita', 'anda', 'kamu',
        'ini', 'itu', 'tersebut',
        'lagi', 'sedang', 'sudah', 'belum', 'akan',
        'ada', 'tidak', 'tak', 'bukan',
        'nya', 'lah', 'kah',
        'sebagai', 'tentang', 'paling', 'lebih', 'kurang',
        'hari', 'minggu', 'bulan', 'tahun',
        'dokter', 'klinik', 'goaria',
    ];

    /**
     * Rank dokumen berdasarkan kemiripan TF-IDF (cosine similarity).
     *
     * @param  array<int, array{id:int|string, text:string, meta?:mixed}>  $documents
     * @return array<int, array{id:int|string, score:float, meta?:mixed}>
     */
    public function rank(string $query, array $documents, int $limit = 3, float $minScore = 0.08): array
    {
        $queryTokens = $this->tokenize($query);
        if ($queryTokens === []) {
            return [];
        }

        $docTokens = [];
        foreach ($documents as $doc) {
            $docTokens[] = $this->tokenize((string) ($doc['text'] ?? ''));
        }

        $idf = $this->computeIdf($docTokens);
        $queryVector = $this->toTfidfVector($queryTokens, $idf);
        $queryNorm = $this->vectorNorm($queryVector);
        if ($queryNorm <= 0.0) {
            return [];
        }

        $scored = [];
        foreach ($documents as $i => $doc) {
            $docVector = $this->toTfidfVector($docTokens[$i] ?? [], $idf);
            $score = $this->cosineSimilarity($queryVector, $queryNorm, $docVector);

            if ($score < $minScore) {
                continue;
            }

            $scored[] = [
                'id' => $doc['id'],
                'score' => $score,
                'meta' => $doc['meta'] ?? null,
            ];
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($scored, 0, max(1, $limit));
    }

    /**
     * @param  array<int, string>  $tokens
     * @return array<string, float>
     */
    private function toTfidfVector(array $tokens, array $idf): array
    {
        if ($tokens === []) {
            return [];
        }

        $counts = [];
        foreach ($tokens as $t) {
            $counts[$t] = ($counts[$t] ?? 0) + 1;
        }

        $len = count($tokens);
        $vector = [];

        foreach ($counts as $term => $count) {
            $tf = $count / $len;
            $termIdf = $idf[$term] ?? 0.0;
            $w = $tf * $termIdf;
            if ($w > 0) {
                $vector[$term] = $w;
            }
        }

        return $vector;
    }

    /**
     * @param  array<int, array<int, string>>  $documentsTokens
     * @return array<string, float>
     */
    private function computeIdf(array $documentsTokens): array
    {
        $n = count($documentsTokens);
        if ($n === 0) {
            return [];
        }

        $df = [];
        foreach ($documentsTokens as $tokens) {
            $seen = [];
            foreach ($tokens as $t) {
                $seen[$t] = true;
            }
            foreach (array_keys($seen) as $t) {
                $df[$t] = ($df[$t] ?? 0) + 1;
            }
        }

        $idf = [];
        foreach ($df as $term => $docFreq) {
            // smooth: log((N+1)/(df+1)) + 1
            $idf[$term] = log(($n + 1) / ($docFreq + 1)) + 1;
        }

        return $idf;
    }

    /**
     * @param  array<string, float>  $vector
     */
    private function vectorNorm(array $vector): float
    {
        $sum = 0.0;
        foreach ($vector as $w) {
            $sum += $w * $w;
        }

        return sqrt($sum);
    }

    /**
     * @param  array<string, float>  $queryVector
     * @param  array<string, float>  $docVector
     */
    private function cosineSimilarity(array $queryVector, float $queryNorm, array $docVector): float
    {
        $docNorm = $this->vectorNorm($docVector);
        if ($docNorm <= 0.0) {
            return 0.0;
        }

        $dot = 0.0;
        foreach ($queryVector as $term => $qw) {
            $dot += $qw * ($docVector[$term] ?? 0.0);
        }

        return $dot / ($queryNorm * $docNorm);
    }

    /**
     * Tokenisasi sederhana (lowercase, buang tanda baca, stopwords, normalisasi akhiran).
     *
     * @return array<int, string>
     */
    private function tokenize(string $text): array
    {
        $text = Str::lower($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text) ?? '';
        $text = preg_replace('/\s+/u', ' ', trim($text)) ?? '';

        if ($text === '') {
            return [];
        }

        $stopwords = self::DEFAULT_STOPWORDS;
        try {
            if (function_exists('config')) {
                $configured = config('chatbot.stopwords');
                if (is_array($configured) && $configured !== []) {
                    $stopwords = $configured;
                }
            }
        } catch (\Throwable $e) {
            // ignore; fallback to defaults
        }
        $stopSet = array_fill_keys(array_map('strval', $stopwords), true);

        $synonyms = [];
        try {
            if (function_exists('config')) {
                $configuredSynonyms = config('chatbot.synonyms');
                if (is_array($configuredSynonyms)) {
                    $synonyms = $configuredSynonyms;
                }
            }
        } catch (\Throwable $e) {
            // ignore; fallback to empty
        }

        $raw = preg_split('/\s+/u', $text) ?: [];
        $tokens = [];

        foreach ($raw as $t) {
            $t = trim($t);
            if ($t === '' || mb_strlen($t) < 2) {
                continue;
            }

            $t = $this->lightStem($t);
            if ($t === '' || isset($stopSet[$t])) {
                continue;
            }

            $tokens[] = $t;

            // Expand sinonim jika ada (mis: "gigi" => ["odont" ...])
            if (isset($synonyms[$t]) && is_array($synonyms[$t])) {
                foreach ($synonyms[$t] as $syn) {
                    $syn = $this->lightStem((string) $syn);
                    if ($syn !== '' && ! isset($stopSet[$syn])) {
                        $tokens[] = $syn;
                    }
                }
            }
        }

        return $tokens;
    }

    /**
     * Stemming ringan untuk Bahasa Indonesia (heuristik sederhana).
     */
    private function lightStem(string $token): string
    {
        $token = trim($token);
        if ($token === '') {
            return '';
        }

        // Hapus akhiran kepemilikan/partikel
        foreach (['lah', 'kah', 'tah', 'pun'] as $suffix) {
            if (Str::endsWith($token, $suffix) && mb_strlen($token) > mb_strlen($suffix) + 2) {
                $token = mb_substr($token, 0, -mb_strlen($suffix));
            }
        }

        foreach (['ku', 'mu', 'nya'] as $suffix) {
            if (Str::endsWith($token, $suffix) && mb_strlen($token) > mb_strlen($suffix) + 2) {
                $token = mb_substr($token, 0, -mb_strlen($suffix));
            }
        }

        // Hapus akhiran umum
        foreach (['kan', 'an', 'i'] as $suffix) {
            if (Str::endsWith($token, $suffix) && mb_strlen($token) > mb_strlen($suffix) + 2) {
                $token = mb_substr($token, 0, -mb_strlen($suffix));
            }
        }

        return $token;
    }
}
