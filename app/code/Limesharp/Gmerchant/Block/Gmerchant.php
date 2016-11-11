<?php

namespace Limesharp\Gmerchant\Block;
use Magento\Store\Model\ScopeInterface;
use Magento\Backend\Block\Template\Context;

class Gmerchant extends \Magento\Framework\View\Element\Template
{
	// const is taken from system section ID, group ID and Field ID
	const GOOGLE_PRODUCT_CATEGORY = "limesharp_gmerchant/gmerchant_general/category";
	const BRAND = "limesharp_gmerchant/gmerchant_general/brand";
	const MANAGE_SHIPPING = "limesharp_gmerchant/gmerchant_general/shipping";
	
	public function __construct(
		Context $context,
		array $data =[]
	){
		parent::__construct($context, $data);
	}
	
	public function getGoogleProductCategory(){
		return $this->_scopeConfig->getValue(self::GOOGLE_PRODUCT_CATEGORY, ScopeInterface::SCOPE_STORE);

	}
	public function getConfigBrand(){
		return $this->_scopeConfig->getValue(self::BRAND, ScopeInterface::SCOPE_STORE);

	}
	public function getmanageShipping(){
		return $this->_scopeConfig->getValue(self::MANAGE_SHIPPING, ScopeInterface::SCOPE_STORE);

	}
}