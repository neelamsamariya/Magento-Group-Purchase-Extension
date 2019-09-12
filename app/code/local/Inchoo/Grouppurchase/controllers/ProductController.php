<?php
require_once 'Mage/Catalog/controllers/ProductController.php';

class Inchoo_Grouppurchase_ProductController extends Mage_Catalog_ProductController
{	
	public function getproductsAction()
	{
		
		$product_arr = array();
		$catid = $this->getRequest()->getParam('catid');   
		$category = new Mage_Catalog_Model_Category();
		$category->load($catid); // this is your category id!
		$collection = $category->getProductCollection();
		$collection->addAttributeToFilter('type_id','configurable');		
		$h = 0;
		if($catid != "")
		{
			foreach($collection as $product) {
				$product = Mage::getModel('catalog/product')->load($product->getId()); /* Load Products by ID*/
			//echo $product->getName();
			//echo $product->getShortDescription();
				if($product->isSaleable()){
				$product_arr[$h]['id'] = $product->getId();
				$product_arr[$h]['name'] = $product->getName();
				$product_arr[$h]['url'] = $product->getProductUrl();
				$h++;
				}
			}
		}
		$jsonData = json_encode($product_arr);
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody($jsonData);
		
	}
	public function getFinalPriceAction()
	{
		
		$product_arr = array();
		$productid = $this->getRequest()->getParam('prodid');   
		$new_price = $this->getRequest()->getParam('newprice');
		
		$model = Mage::getModel('catalog/product'); //getting product model
		$_product = $model->load($productid); //getting product object for particular product id
		$currency_symbol = Mage::app()->getLocale()->currency( $currency_code )->getSymbol();
		$price_arr = array();
		$price_arr['plain_price'] = $_product->getFinalPrice();
		$price_new = $new_price * $price_arr['plain_price'];
		$price_arr['formatted_price'] = number_format($_product->getFinalPrice(), '2', '.', ',');
		$price_arr['new_formatted_price'] = $currency_symbol.number_format($price_new, '2', '.', ',');
		$price_arr['unformatted_price'] = $price_new;	
		
		$jsonData = json_encode($price_arr);
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody($jsonData);
	
	}

	public function getTotalPriceFormattedAction()
	{
		$price = $this->getRequest()->getParam('calc_price'); 
		$currency_symbol = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol(); 
		$formated_price = $currency_symbol.number_format($price, '2', '.', ',');		
		$jsonData = json_encode($formated_price);
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody($jsonData);
	}
	public function customAddToCartAction()
	{
		//$products_array = Mage::helper('core')->jsonDecode($p_arr);
		$products_array = $this->getRequest()->getParam('p_arr');
		$flag = 0;
		//print_r($products_array);
		//exit;
		if(count($products_array) > 0)
		{
			$cart = Mage::helper('checkout/cart')->getCart();
			for($i=0;$i<count($products_array);$i++)
			{
				$product_id = $products_array[$i]['prod_id'];
				$attr_val = $products_array[$i]['lang'];
				/*$product = Mage::getModel('catalog/product')->load($product_id);
				$productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
				$attributeOptions = array();
				foreach ($productAttributeOptions as $productAttribute) {					
					$attributeOptions['attributes']['id'] = $productAttribute['attribute_id'];					
				}*/
				$attributeInfo = Mage::getResourceModel('eav/entity_attribute_collection')
					->setCodeFilter('languages')
					->getFirstItem();
				$attr_id = $attributeInfo->getId();
				
        		 	$quantity = $products_array[$i]['qty'];
				
				$params = array('qty' => $quantity,
				 'super_attribute' => array(
						$attr_id => $attr_val ,
					)
				 );
				
				 $product = Mage::getModel('catalog/product')->load($product_id);
				 $cart->addProduct($product, $params);
			}
	    $msg = "Courses Added Successfully to the cart";		
	    $cart->save();
            Mage::getSingleton('core/session')->addSuccess(Mage::helper('checkout')->__($msg));
            //echo $this->_redirect('checkout/cart');
			$flag = 1;
			echo $flag;
		}
		//$jsonData = json_encode($products_array);
		//$this->getResponse()->setHeader('Content-type', 'application/json');
		//$this->getResponse()->setBody($jsonData);
	}
	
	public function getproductsInfoAction()
	{
		$product_arr = array();
		$catid = $this->getRequest()->getParam('catid');   
		$category = new Mage_Catalog_Model_Category();
		$category->load($catid); // this is your category id!
		$collection = $category->getProductCollection();
		$collection->addAttributeToFilter('type_id','configurable');			
		$h = 0;
			if($catid != "")
			{
				$lang_value = "";				
				foreach($collection as $product) {
				$product = Mage::getModel('catalog/product')->load($product->getId()); /* Load Products by ID*/				
				
				if ($product->getData('type_id') == "configurable")
				{
						//get the configurable data from the product
						$config = $product->getTypeInstance(true);
						
						//loop through the attributes
						foreach($config->getConfigurableAttributesAsArray($product) as $attributes)
						{
							//$product_arr[$h]['attributes']['name'] = "super_attribute[".$attributes['attribute_id']."]";
							$product_arr[$h]['attributes']['id'] = $attributes['attribute_id'];
							$product_arr[$h]['attributes']['options'] = "";
							$t=0;
							foreach($attributes["values"] as $values)
							{
								//echo "<option>".$values["label"]."</option>";
								$product_arr[$h]['attributes']['options'][$t]['value'] = $values["value_index"];
								$product_arr[$h]['attributes']['options'][$t]['label'] = $values["label"];
								$t++;
							}
							
						}
				}
				$_price = Mage::getModel('catalogrule/rule')->calcProductPriceRule($product, $product->getPrice());				
				$_finalprice = Mage::helper('core')->currency(Mage::helper('tax')->getPrice($product, $product->getFinalPrice()),true,false);
				$per_req = $product->getResource()->getAttribute("perc_req_pass");
				if ($per_req)
				{
					$perc_value = $per_req->getFrontend()->getValue($product);
				}
				$test_attmpt = $product->getResource()->getAttribute("test_attempts");
				if ($test_attmpt)
				{
					$attmpt_value = $test_attmpt->getFrontend()->getValue($product);
				}
				$avail_lang = $product->getResource()->getAttribute("available_languages");
				if ($avail_lang)
				{
					$lang_value .= $avail_lang->getFrontend()->getValue($product).',';		
					/*if($lang_value != "")
					{
						$lang_exp = explode(',',$lang_value);
						
					}*/
					//$lan = htmlentities($lang_value);
				}
				
				//echo $product->getName();
				//echo $product->getShortDescription();
				$product_arr[$h]['id'] = $product->getId();
				$product_arr[$h]['name'] = $product->getName();
				$product_arr[$h]['finalprice'] = $_finalprice;
				$product_arr[$h]['perct_req'] = $perc_value;
				$product_arr[$h]['attmpt_value'] = $attmpt_value;
				$product_arr[$h]['avail_lang'] = $lang_value;
				$product_arr[$h]['url'] = $product->getProductUrl();
				if ($product->getData('type_id') == "configurable")
				{
					$course_info = $product->getResource()->getAttribute("training_approved_by");
					if ($course_info)
					{						
						$product_arr[$h]['course_info'] = $course_info->getFrontend()->getValue($product);
					}
					$course_ext_info = $product->getResource()->getAttribute("additional_information");
					
					if ($course_ext_info)
					{			
						
						$product_arr[$h]['course_extra_info'] = $course_ext_info->getFrontend()->getValue($product);
						
					}
				}
				if($product->isSaleable()){
					$product_arr[$h]['stock'] = 1;
				}
				else{
					$product_arr[$h]['stock'] = 0;
				}				
				$h++;
				}
			}
		$jsonData = json_encode($product_arr);
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody($jsonData);
	}
	
	
}
