<?php

/** 1) Get a list of all orders in Inksoft.
*   2) Get a list of all orders in Magento.
*   3) Compare the two and see what orders exist in Inksoft and not in Magento
*   4) For every order not in Magento:
*	*if Email exists within Magento:
*		*create an order object with details from Inksoft in XML
*		*attach the order to the email address
*
*   Set up to run every 5 minutes because Inksoft has no way of notifying Magento orders occured.
* ASSUMPTIONS:
* 1) All magento/inksoft/RTD order ID's are unique
* 2) Orders are attached to email addresses that exist. If they do not, then no operation occurs.
* 3) Errors do not break the system as it is, but testing and huge bugs are in place in the code.
**/

require_once '../app/Mage.php';
Mage::app();

// #1 Get a list of all orders in Inksoft

// Inksoft unique API KEY
$data = array(
    'APIKey' => 'FOO'
);

echo "Getting list from inksoft <br>";

// Create a connection
$url = 'http://stores.inksoft.com/OrderList/<foo store number>';
$ch = curl_init($url);

// Form data string
$postString = http_build_query($data, '', '&');

// Setting our options
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Get the response
$response = curl_exec($ch);

echo "New orders found: <br>";

//Close connection to inksoft
curl_close($ch);

//create Array from XML
$xml = simplexml_load_string($response);
$json = json_encode($xml);
$inksoftXMLArray = json_decode($json,TRUE);

$allOrdersFromInksoft;
//For each XML element containing an order, add to array containing only orders. Makes it easer to compare later.
foreach ($inksoftXMLArray['orders'] as $ArrayID) {
$allOrdersFromInksoft[] = $ArrayID['@attributes']['order_id'];
}

// #2 Get all Orders from Magento

//Create model
$allOrdersFromMagneto;

$orders = Mage::getModel('sales/order')->getCollection();
//For each existing model, add to array containing only orders.
foreach ($orders as $order) {
$allOrdersFromMagento[] = $order->increment_id;
}

// #3 Compare the two and get comparisionresult
$comparisionResult = array_diff($allOrdersFromInksoft,$allOrdersFromMagento);

print_r($comparisionResult);


foreach($comparisionResult as $neworderID) {

echo "<br>Entering order into Magento: ".$neworderID."<br>" ;

// Create a connection
$url = 'http://stores.inksoft.com/OrderDetails/<foo store number>'.$neworderID;
$ch = curl_init($url);

// Form data string
$postString = http_build_query($data, '', '&');

// Setting our options
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Get the response
$response = curl_exec($ch);

//TESTING
//echo "this is response:";
//var_dump(curl_exec($ch));
//var_dump($response);

//Close connection to inksoft
curl_close($ch);

$xml = simplexml_load_string($response);
$json = json_encode($xml);
$array = json_decode($json,TRUE);

//TESTING
//var_dump($array);

/**
 * All Data from Inksoft
 * Push all data into values to be used later
 * ALL ORDER DATA
 */
$orderID		=		($array['orders']['@attributes']['order_id']);
$orderPublisherID	=		($array['orders']['@attributes']['publisher_id']);
$orderStoreID		=		($array['orders']['@attributes']['store_id']);
$orderUserID            =               ($array['orders']['@attributes']['user_id']);
$orderShippingMethod	=		($array['orders']['@attributes']['shipping_method']);
$orderShippingMethodID	=		($array['orders']['@attributes']['shipping_method_id']);
$orderRetailCost	=		($array['orders']['@attributes']['retail_cost']);
$orderRetailAmount	=		($array['orders']['@attributes']['retail_amount']);
$orderShippingAmount	=		($array['orders']['@attributes']['shipping_amount']);
//$orderProcessMarkupAmount = 		($array['orders']['@attributes']['process_markup_amount']); 	//Not needed for orders on Magneto
$orderTaxAmount		=		($array['orders']['@attributes']['tax_amount']);
$orderTotalAmount	=		($array['orders']['@attributes']['total_amount']);
//$orderGiftCertificateAmount = 	($array['orders']['@attributes']['giftcert_amount']); 		//Optional field
$orderAmountDue		=		($array['orders']['@attributes']['amount_due']);
//$orderIPAddress	=		($array['orders']['@attributes']['ip_address']);		//Optional field
$orderDateCreated	=		($array['orders']['@attributes']['date_created']);
$orderLastModified	=		($array['orders']['@attributes']['last_modified']);
$orderEmail		=		($array['orders']['@attributes']['email']);
$orderProcessAmount	=		($array['orders']['@attributes']['process_amount']);
$orderPaymentMethod	=		($array['orders']['@attributes']['payment_method']);
$orderAuthorized	=		($array['orders']['@attributes']['authorized']);

//Admin settings that get pushed from inksoft to Magento. Not nessessary but left in for advanced properties
$orderConfirmed		=		($array['orders']['@attributes']['authorized']);
$orderRendered		=		($array['orders']['@attributes']['rendered']);
$orderOrdered		=		($array['orders']['@attributes']['ordered']);
$orderReceived		=		($array['orders']['@attributes']['received']);
$orderPrepared		=		($array['orders']['@attributes']['prepared']);
$orderPaid		=		($array['orders']['@attributes']['paid']);
$orderShipped		=		($array['orders']['@attributes']['shipped']);
$orderCancelled		=		($array['orders']['@attributes']['cancelled']);

//More order info
$orderEstimatedShipDate	=		($array['orders']['@attributes']['estimated_ship_date']);
$orderEstimatedDeliveryMinDate	=	($array['orders']['@attributes']['estimated_delivery_min_date']);
$orderEstimatedDeliveryMaxDate	=	($array['orders']['@attributes']['estimated_delivery_max_date']);
$orderCurrencyCode		=	($array['orders']['@attributes']['currency_code']);

//BILLING ADDRESS:
// 			has its own xml. Parse though and get relivent info.
$billingAddressXML	=		($array['orders']['@attributes']['billing_address_x005F_xml']);
$xml = simplexml_load_string($billingAddressXML);
$json = json_encode($xml);
$billingArray = json_decode($json,TRUE);

//TESTING
//var_dump($billingArray);

//relevent info on Billing
$billingAddressID	=		($billingArray['@attributes']['address_id']);
$billingUserID		=               ($billingArray['@attributes']['user_id']);
$billingFirstName	=		($billingArray['@attributes']['first_name']);
$billingLastName	=		($billingArray['@attributes']['last_name']);
$billingStreet		=		($billingArray['@attributes']['street1']);
$billingStreet2		=		($billingArray['@attributes']['street2']); //Probably not needed but added anyways
$billingCity		=		($billingArray['@attributes']['city']);
$billingStateID		=		($billingArray['@attributes']['state_id']);
$billingPostCode	=		($billingArray['@attributes']['postcode']);
$billingCountryID	=		($billingArray['@attributes']['country_id']);
$billingPhoneNumber	=		($billingArray['@attributes']['phone']);
$billingBuisness	=		($billingArray['@attributes']['business']);
$billingValidated	=		($billingArray['@attributes']['validated']);
$billingDateCreated	=		($billingArray['@attributes']['date_created']);
$billingDateModified	=		($billingArray['@attributes']['last_modified']);
$billingPOBOX		=		($billingArray['@attributes']['pobox']);
$billingDeleted		=		($billingArray['@attributes']['deleted']);


// SHIPPING ADDRESS
//                      has its own xml. Parse though and get relivent info.
$shippingAddressXML      =               ($array['orders']['@attributes']['shipping_address_x005F_xml']);
$xml = simplexml_load_string($shippingAddressXML);
$json = json_encode($xml);
$shippingArray = json_decode($json,TRUE);

//TESTING
//var_dump($shippingArray);

//relevent info on Shipping
$shippingAddressID	=		($shippingArray['@attributes']['address_id']);
$shippingUserID		=		($shippingArray['@attributes']['user_id']);
$shippingFirstName	=		($shippingArray['@attributes']['first_name']);
$shippingLastName	=		($shippingArray['@attributes']['last_name']);
$shippingStreet         =               ($shippingArray['@attributes']['street1']);
$shippingStreet2	=		($shippingArray['@attributes']['street2']); ////Probably not needed but added anyways
$shippingCity		=		($shippingArray['@attributes']['city']);
$shippingStateID	=		($shippingArray['@attributes']['state_id']);
$shippingPostcode	=		($shippingArray['@attributes']['postcode']);
$shippingCountryID	=		($shippingArray['@attributes']['country_id']);
$shippingPhone		=		($shippingArray['@attributes']['phone']);
$shippingBusiness	=		($shippingArray['@attributes']['buisness']);
$shippingValidated	=		($shippingArray['@attributes']['validated']);
$shippingDateCreated	=		($shippingArray['@attributes']['date_created']);
$shippingLastModified	=		($shippingArray['@attributes']['last_modified']);
$shippingPOBOX		=		($shippingArray['@attributes']['pobox']);
$shippingDeleted	=		($shippingArray['@attributes']['deleted']);

// At this point all data is recieved from Inksoft. 
// We wil now create the objects for Magento to add to an order

// Do operation if customer email exists and the order is not already in the system.
$customer = Mage::getModel("customer/customer");
$customer->setWebsiteId(Mage::app()->getWebsite()->getId());
$customer->loadByEmail($orderEmail);

if ($customer->getId()){
echo "Customer exists \n";
	//We assume the orderID is unique
echo "Exporting order to foo.com \n";

$transaction = Mage::getModel('core/resource_transaction');
$storeId = $customer->getStoreId();

$order = Mage::getModel('sales/order')
->setIncrementId($neworderID)
//->setIncrementId("2000215367") //Manual adding of Mageto Keys
->setStoreId(1)
->setQuoteId(0)
->setGlobal_currency_code('USD')
->setBase_currency_code('USD')
->setStore_currency_code('USD')
->setOrder_currency_code('USD');

//Get Customer Data
$order->setCustomer_email($customer->getEmail())
->setCustomerFirstname($customer->getFirstname())
->setCustomerLastname($customer->getLastname())
->setCustomerGroupId($customer->getGroupId())
->setCustomer_is_guest(0)
->setCustomer($customer);

// set Billing Address
$billing = $customer->getDefaultBillingAddress();
$billingAddress = Mage::getModel('sales/order_address')
->setStoreId(1)
//->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_BILLING)
//->setCustomerId($customer->getId())
//->setCustomerAddressId($customer->getDefaultBilling())
//->setCustomer_address_id($billing->getEntityId())
//->setPrefix($billing->getPrefix())
->setFirstname($billingFirstName)
->setMiddlename($billing->getMiddlename())
->setLastname($billingLastName)
//->setSuffix($billing->getSuffix())
->setCompany($billing->getCompany())
->setStreet($billingStreet)
->setCity($billingCity)
//->setCountry_id($billingCountryID)
//->setRegion($billing->getRegion())
//->setRegion_id($billing->getRegionId())
->setPostcode($billingPostCode)
->setTelephone($billingPhoneNumber);
//->setFax($billing->getFax())*/;
$order->setBillingAddress($billingAddress);

//Get shipping address
$shipping = $customer->getDefaultShippingAddress();
//var_dump($shipping);

$shippingAddress = Mage::getModel('sales/order_address')
->setStoreId(1)
//->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_SHIPPING)
//->setCustomerId($customer->getId())
//->setCustomerAddressId($customer->getDefaultShipping())
//->setCustomer_address_id($shipping->getEntityId())
//->setPrefix($shipping->getPrefix())
->setFirstname($billingFirstName)
//->setMiddlename($shipping->getMiddlename())
->setLastname($shippingLastName)
//->setSuffix($shipping->getSuffix())
//->setCompany($shippingBuisness)
->setStreet($shippingStreet)
->setCity($shippingCity)
//->setCountry_id($shippingCountryID)
//->setRegion($shipping->getRegion())
//->setRegion_id($shipping->getRegionId())
->setPostcode($shippingPostcode)
->setTelephone($shippingPhone);
//->setFax($shipping->getFax())*/;

$order->setShippingAddress($shippingAddress)
->setShipping_method($orderShippingMethodID)
->setShippingDescription($orderShippingMethod);

$orderPayment = Mage::getModel('sales/order_payment')
->setStoreId(1)
->setCustomerPaymentId(0)
->setMethod('purchaseorder')
->setPo_number(' - ');
$order->setPayment($orderPayment);

$order->setSubtotal($orderAmountDue)
->setBaseSubtotal($orderAmountDue)
->setGrandTotal($orderAmountDue)
->setBaseGrandTotal($orderAmountDue);


$transaction->addObject($order);
$transaction->addCommitCallback(array($order, 'place'));
$transaction->addCommitCallback(array($order, 'save'));
$transaction->save(); //comment out if testing or errors will occur


echo "<br> order successfully entered<br>";

}
else{
echo "no customer email in database. Nothing to do here";
}

}//end foreach

exit(0);
?>
