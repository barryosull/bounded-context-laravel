<?php namespace BoundedContext\Laravel\Sourced\Aggregate\Stream;

use BoundedContext\Contracts\ValueObject\Identifier;
use BoundedContext\ValueObject\Integer as Integer_;
use Illuminate\Contracts\Foundation\Application;

class Factory implements \BoundedContext\Contracts\Sourced\Aggregate\Stream\Factory
{
    private $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    public function create(
        Identifier $aggregate_id,
        Identifier $aggregate_type_id,
        Integer_ $starting_offset,
        Integer_ $limit,
        Integer_ $chunk_size
    )
    {        
        return $this->application->make(
            'BoundedContext\Laravel\Sourced\Aggregate\Stream\Stream',
            [
                $this->application->make('db')->connection(),
                $this->application->make('BoundedContext\Laravel\Event\Snapshot\Factory'),
                $aggregate_id,
                $aggregate_type_id,
                $starting_offset,
                $limit,
                $chunk_size
            ]
        );
    }
}
