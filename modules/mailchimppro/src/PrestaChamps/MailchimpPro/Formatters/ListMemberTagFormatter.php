<?php
/**
 * PrestaChamps
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Commercial License
 * you can't distribute, modify or sell this code
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file
 * If you need help please contact leo@prestachamps.com
 *
 * @author    PrestaChamps <leo@prestachamps.com>
 * @copyright PrestaChamps
 * @license   commercial
 */

namespace PrestaChamps\MailchimpPro\Formatters;
if (!defined('_PS_VERSION_')) {
    exit;
}
/**
 * Class ListMemberTagFormatter
 *
 * @package PrestaChamps\MailchimpPro\Formatters
 */
class ListMemberTagFormatter
{
	const STATUS_ACTIVE = 'active';
	const STATUS_INACTIVE = 'inactive';

	public $tags;

    /**
     * ListMemberTagFormatter constructor.
     *
	 * @param $tag
     */
    public function __construct($tags)
    {
		$this->tags = $tags;        
    }

    /**
     * @return array
     */
    public function format()
    {
        $data = [
            'tags' => $this->tags,
        ];

        return $data;
    }
}
