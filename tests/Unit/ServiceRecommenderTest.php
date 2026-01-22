<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Nlp\ServiceRecommender;

class ServiceRecommenderTest extends TestCase
{
    public function test_it_ranks_more_relevant_service_higher(): void
    {
        $recommender = new ServiceRecommender();

        $documents = [
            ['id' => 1, 'text' => 'Pembersihan karang gigi scaling pembersihan plak'],
            ['id' => 2, 'text' => 'Pemasangan behel ortodonti kontrol bracket'],
            ['id' => 3, 'text' => 'Penambalan gigi tambal gigi berlubang'],
        ];

        $ranked = $recommender->rank('ingin pasang behel ortodonti', $documents, limit: 2, minScore: 0.0);

        $this->assertNotEmpty($ranked);
        $this->assertSame(2, $ranked[0]['id']);
    }
}
