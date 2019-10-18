# PHP Attribute Based Access Control (ABAC)
A simple framework for implementing ABAC in your application.

# Rules
Rules implement business logic, the input for rule execution consists of:
- source: The actor, usually the current user
- target: The subject, the entity that the actor wishes to act upon
- permission: The action the actor wishes to take
- environment: The environment should contain anything else the business rules may need

Rules are encourage to do recursive access check. A typical rule could be `WriteImpliesRead`, since for most systems when you can write an object you can also read it.
Implementation could look like this:
```php
public function execute(
    object $source,
    object $target,
    string $permission,
    Environment $environment,
    AccessChecker $accessChecker
): bool {
    return $permission === 'read' && $accessChecker->check($source, $target, 'write');
}
```
 
## Environment
Consider a rule that allows access only during office hours. The current time should then be set in the environment.
Reasoning behind this is that having 1 location for the environment allows for easy testing as well as a single source of truth.

# Infinite loops
Rules can contain infinite loops, we track recursion depth to detect these loops.
