<?php
declare(strict_types=1);

namespace SamIT\abac\connectors\yii2;

use SamIT\abac\AuthManager;
use SamIT\abac\interfaces\AccessChecker;
use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Resolver;
use yii\base\InvalidConfigException;
use yii\web\IdentityInterface;

class Manager  implements \yii\rbac\CheckAccessInterface, \yii\base\Configurable
{
    public const TARGET_PARAM = 'target';
    public const GLOBAL = '__global__';

    /**
     * @var AccessChecker
     */
    private $accessChecker;

    /**
     * @var Resolver
     */
    private $resolver;

    /**
     * @var string Auth name to use for permission checks without target
     */
    private $globalName = self::GLOBAL;

    /**
     * @var string Auth id to use for permission checks without target
     */
    private $globalId = self::GLOBAL;

    /**
     * @var string Name of a class that implements IdentityInterface
     */
    private $userClass;

    /**
     * @var array Mapping of user IDs to user objects
     */
    private $userCache = [];

    public function __construct(
        AccessChecker $accessChecker,
        Resolver $resolver,
        $config = []
    ) {
        $this->accessChecker = $accessChecker;
        $this->resolver = $resolver;

        foreach($config as $key => $value) {
            $this->$key = $value;
        }
        $this->init();
    }

    public function init()
    {
        if (!isset($this->userClass)) {
            throw new \yii\base\InvalidConfigException("userClass must be configured.");
        }
        if (!is_subclass_of($this->userClass, IdentityInterface::class, true)) {
            throw new InvalidConfigException("userClass must implement IdentityInterface");
        }
    }


    /**
     * @return \ArrayObject
     */
    protected function getEnvironment()
    {
        return new \ArrayObject([
            'app' => \Yii::$app,
            'identity' => \Yii::$app->user->identity
        ]);
    }

    protected function getUser(string $id): ?IdentityInterface
    {
        $key = "{$this->userClass}|$id";
        if (!array_key_exists($key, $this->userCache)) {
            $this->userCache[$key] = $this->userClass::findIdentity($id);
        }

        return $this->userCache[$key];
    }

    /**
     * @param int|string $userId
     * @param string $permissionName
     * @param array $params
     * @return bool
     */
    public function checkAccess($userId, $permissionName, $params = [])
    {
        $user = $this->getUser($userId);
        $source = $this->resolver->fromSubject($user);

        $target = $this->resolver->fromSubject($params[self::TARGET_PARAM])
            ?? new \SamIT\abac\Authorizable($this->globalId, $this->globalName);


        return $this->manager->resolveAndCheck($source, $target, $permissionName);
    }


}