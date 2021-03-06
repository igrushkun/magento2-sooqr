<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\Sooqr\Model\System\Config\Source\Configurable;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Link
 *
 * @package Magmodules\Sooqr\Model\System\Config\Source\Configurable
 */
class Link implements ArrayInterface
{

    /**
     * Options array
     *
     * @var array
     */
    public $options = null;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [
                ['value' => '0', 'label' => __('No')],
                ['value' => '1', 'label' => __('Yes')],
                ['value' => '2', 'label' => __('Yes, with Auto-Link (Recommended)')],
            ];
        }
        return $this->options;
    }
}
