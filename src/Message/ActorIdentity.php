<?php
/**
 * Created by PhpStorm.
 * User: imhotek
 * Date: 18/04/18
 * Time: 08:47
 */

namespace Carnage\Phactor\Message;


use Carnage\Phactor\Actor\ActorInterface;

final class ActorIdentity
{
    private $class;

    private $id;

    public function __construct(string $class, string $id)
    {
        $this->class = $class;
        $this->id = $id;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public static function fromActor(ActorInterface $actor)
    {
        return new self(get_class($actor), $actor->id());
    }
}
