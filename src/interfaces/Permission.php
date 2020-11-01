<?php


namespace SamIT\abac\interfaces;

interface Permission
{
    public function getSourceName(): string;
    public function getSourceId(): string;
    public function getTargetName(): string;
    public function getTargetId(): string;
    public function getId(): string;

    public function getSource(): ?Authorizable;
    public function getTarget(): ?Authorizable;
}
