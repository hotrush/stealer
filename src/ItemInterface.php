<?php

namespace Hotrush\Stealer;

interface ItemInterface
{
    public function processItem(AdaptersRegistry $adaptersRegistry);
}