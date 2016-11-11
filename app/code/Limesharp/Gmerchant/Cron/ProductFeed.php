<?php 
	// Gmerchant Product Feed Module
namespace Limesharp\Gmerchant\Cron;

use Limesharp\Gmerchant\Block\Gmerchant;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class ProductFeed
{
	public $productFactory;

	public $gmerchantSettings;
	
	public $collectionFactory;
	
	public $directoryList;
	
	public $fieldsHeader = array(
		'ID',
		'title',
		'description',
		'price',
		'sale_price',
		'condition',
		'link',
		'availability',
		'image_link',
		'brand',
		'gtin',
		'identifier_exists',
		'google_product_category',
		'product_type',
		'size',
		'color',
		'item_group_id',
		'gender',
		'tax',
		'shipping'
	);
	
	public function __construct(
		ProductFactory $productFactoryInstance,
		Gmerchant $gmerchantSettings,
		CollectionFactory $collectionFactory,
		DirectoryList $directoryList
	){
		$this->productFactory = $productFactoryInstance;
		$this->gmerchantSettings = $gmerchantSettings;
		$this->collectionFactory = $collectionFactory;
		$this->directoryList = $directoryList;
	}
	
	// This is the method
	public function execute(){
		$fullCatalog = $this->collectionFactory->create()
		->addAttributeToSelect('*')
		->addFieldToFilter("visibility", 4)
		->load();
		
		$allProducts = [];
		foreach($fullCatalog as $product){
			foreach($this->processProducts($product) as $individualProduct){
				$allProducts[] = $individualProduct;
			}
		}
			$this->handleFiles($allProducts); 
	}
	
	public function add_quotes($str) {
		    return sprintf('"%s"', $str);
		}
		
	public function handleFiles($productData){
		$mediaPath = $this->directoryList->getPath('media');
		
		$file = fopen($mediaPath . "/gmerchant.txt", "w");
		$prepareRow =[];
		foreach($productData as $product){
			$prepareRow[] = implode("\t", array_map(array($this, 'add_quotes'), $product));
		}
		fwrite($file, implode("\n", $prepareRow));
	}
	
	
	public function processBrands($firstProduct){
		if($firstProduct->getBrand()){
			return $firstProduct->getBrand();
		} else {
			return $this->gmerchantSettings->getConfigBrand(); // This is the Brand from the Backend
		}
	}
	
	public function manageIdentifiers($firstProduct){
		if($this->processBrands($firstProduct) && $firstProduct->getGtin()){
			return 'TRUE';
		} else {
			return 'FALSE';
		}
	}
	
	public function manageShipping($firstproduct){
		return $this->gmerchantSettings->getmanageShipping();
	}
	
	public function getTax(){
		return '';
	}
	
	public function processProducts($product){
		$productData = [];
		$productData["sku"] = $product->getSku();
		$productData["title"] = $product->getName();
		$productData["description"] = strip_tags($product->getDescription());
		$productData["price"] = $product->getPrice();
		$productData["sale_price"] = $product->getSpecialPrice(); 
		$productData["condition"] = "new";
		$productData["link"] = $product->getUrlKey();
		$productData["availability"] = $product->getIsInStock();
		$productData["image_link"] = $product->getImageUrl();
		$productData["brand"] = $this->processBrands($product);
		$productData["gtin"] = $product->getGtin(); // This needs to be created
		$productData["identifier_exists"] = $this->manageIdentifiers($product);// Needs to be created
		$productData["google_product_category"] = $this->gmerchantSettings->getGoogleProductCategory();
		$productData["product_type"] = $product->getProductType();
		$productData["size"] = $product->getSize();
		$productData["color"] = $product->getColor();
		$productData["gender"] = $product->getGender();
		$productData["tax"] = $this->getTax();
		$productData["shipping"] = $this->manageShipping($product);	
		
		if($product->getTypeId() == 'configurable'){
			$childrenData = $productData;
			$childrenProducts = $product->getTypeInstance()->getUsedProducts($product);
			$allProducts = [];
			if(!empty($childrenProducts)){
				foreach($childrenProducts as $childProduct){
					if($childProduct->getStatus() == 1){ // if the product is enabled
						$childrenData["sku"] = $childProduct->getSku();
						$childrenData["title"] = $childProduct->getName();
						$childrenData["price"] = $childProduct->getPrice();
						$childrenData["sale_price"] = $childProduct->getSpecialPrice();
						$childrenData["availability"] = $childProduct->getIsInStock();
						$childrenData["size"] = $childProduct->getSize();
						$childrenData["color"] = $childProduct->getColor();
						$childrenData["item_group_id"] = $product->getSku();
						
						$allProducts[] = $childrenData;
					}
				}
			}
		}
		$allProducts[] = $productData;

		return $allProducts;

	}
	
}
