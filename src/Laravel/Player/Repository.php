<?php namespace BoundedContext\Laravel\Player;

use BoundedContext\Contracts\Player\Player;
use BoundedContext\Contracts\ValueObject\Identifier;
use BoundedContext\Player\Snapshot\Factory;
use Illuminate\Contracts\Foundation\Application;

class Repository implements \BoundedContext\Contracts\Player\Repository
{
    private $app;
    private $connection;
    private $table;
    private $player_factory;
    private $snapshot_factory;

    public function __construct(Application $app, Factory $snapshot_factory)
    {
        $this->app = $app;
        $this->connection = $app->make('db');

        $this->table = $app->make('config')->get(
            'bounded-context.database.tables.players'
        );

        $this->player_factory = $app->make('BoundedContext\Laravel\Player\Factory');

        $this->snapshot_factory = $snapshot_factory;
    }

    protected function query()
    {
        return $this->connection->table($this->table);
    }

    public function get(Identifier $namespace)
    {
        $row = $this->query()
            ->sharedLock()
            ->where('name', $namespace->serialize())
            ->first();

        if(!$row)
        {
            throw new \Exception("The Projector [".$namespace->serialize()."] does not exist.");
        }

        return $this->player_factory->id(
            $namespace,
            $this->snapshot_factory->make($row)
        );
    }

    public function save(Player $player)
    {
        $class_name = get_class($player);

        $this->query()
            ->where('name', $class_name)
            ->update(
                $player->snapshot()->serialize()
            );
    }
}