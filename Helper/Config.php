<?php

namespace Howard\NewsletterCoupon\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;

class Config extends AbstractHelper
{

	const MODULE_PATH = 'newslettercoupon/';

  public function __construct(
    Context $context
  ){
    parent::__construct($context);
  }
	public function getModuleConfig($path, $storeId = null)
	{
    return $this->scopeConfig->getValue(
        self::MODULE_PATH . $path,
        ScopeInterface::SCOPE_STORE,
        $storeId
      );
	}

  public function isEnabled()
  {
    return (bool)$this->getModuleConfig('general/enable');
  }

  public function ruleId(){
    return $this->getModuleConfig('general/rule');
  }

}
