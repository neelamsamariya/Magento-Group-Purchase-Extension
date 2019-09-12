<?php
class Inchoo_Grouppurchase_Helper_Data extends Mage_Core_Helper_Abstract
{    
	public function getCategoriesDropdown() {
		
		$categories = Mage::getModel('catalog/category')
			->getCollection()			
			->addAttributeToSelect('name')
			->addAttributeToSort('path', 'asc');
	
		$first = array();
		$children = array();
		$root_cat = array();	
	
		$j=0;
		$h=0;
		$cat_fetch = array('11','12');
		foreach ($categories->getItems() as $cat) {
			
			if ($cat->getLevel() == 2) {
				$cid = $cat->getId();
				
				if (in_array($cid, $cat_fetch)){					
				$first[$cat->getId()] = $cat;
				$root_cat[$h]['cat_id'] = $cat->getId();
				$root_cat[$h]['cat_name'] = $cat->getName();		
				$h++;	
				}				
							
			} else if ($cat->getParentId()) {
				$children[$cat->getParentId()][] = $cat->getData();	
				$category[$cat->getParentId()]['child_'.$j] = $cat->getId();			
				$j++;
			}
		}
		
		return array('first' => $first, 'children' => $children, 'root_categories' => $root_cat);
	}
	public function CategoriesPageDropdown() {
    $categories = Mage::getModel('catalog/category')
        ->getCollection()
        ->addAttributeToSelect('name')
        ->addAttributeToSelect('image')
        ->addAttributeToSort('path', 'asc')
        ->addFieldToFilter('is_active', array('eq'=>'1'));

    $first = array();
    $children = array();
    foreach ($categories->getItems() as $cat) {
        if ($cat->getLevel() == 2) {
            $first[$cat->getId()] = $cat;
        } else if ($cat->getParentId()) {
            $children[$cat->getParentId()][] = $cat->getData();
        }
    }

    return array('first' => $first, 'children' => $children);
}

	
}
?>