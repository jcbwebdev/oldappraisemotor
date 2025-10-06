<?php

    namespace PeterBourneComms\Ecommerce;

    use PeterBourneComms\CMS\Database;
    use PeterBourneComms\Ecommerce\Country;
    use PeterBourneComms\Ecommerce\Product;
    use PeterBourneComms\CMS\Member;
    use PDO;
    use PDOException;
    use Exception;

    /**
     * Deals with Orders
     *
     *
     * @author Peter Bourne
     * @version 1.0
     *
     *          1.0     06.08.2020  Original version
     *
     */
    class Order
    {
        /**
         * @var mixed
         */
        protected $_dbconn;
        /**
         * @var int
         */
        protected $_id;
        /**
         * @var
         */
        protected $_member_id;
        protected $_dely_firstname;
        protected $_dely_surname;
        protected $_dely_organisation;
        protected $_dely_address1;
        protected $_dely_address2;
        protected $_dely_town;
        protected $_dely_county;
        protected $_dely_postcode;
        protected $_dely_country;
        protected $_dely_country_code;
        protected $_dely_email;
        protected $_dely_telephone;
        protected $_dely_mobile;
        protected $_inv_firstname;
        protected $_inv_surname;
        protected $_inv_organisation;
        protected $_inv_address1;
        protected $_inv_address2;
        protected $_inv_town;
        protected $_inv_county;
        protected $_inv_postcode;
        protected $_inv_country;
        protected $_inv_country_code;
        protected $_inv_email;
        protected $_inv_telephone;
        protected $_inv_mobile;

        protected $_order_date;
        protected $_order_gross;
        protected $_order_vat;
        protected $_order_nett;
        protected $_voucher_code;
        protected $_invoice_number;
        protected $_invoice_date;
        protected $_payment_method;
        protected $_paid;
        protected $_date_paid;
        protected $_status;
        protected $_status_detail;
        protected $_despatched;
        protected $_despatch_date;
        protected $_ip;

        protected $_stripe_payment_intent_id;
        protected $_stripe_payment_method_id;
        protected $_stripe_result;

        protected $_order_details;

        protected $_img_path;


        /**
         * Order constructor.
         *
         * @param null $id
         *
         * @throws Exception
         */
        public function __construct($id = null)
        {
            //Connect to database
            if (!$this->_dbconn) {
                try {
                    $conn = new Database();
                    $this->_dbconn = $conn->getConnection();
                } catch (Exception $e) {
                    //handle the exception
                    die;
                }
                //Assess passed id
                if (isset($id) && !is_numeric($id)) {
                    throw new Exception('Class Order requires id to be specified as an integer');
                }
                
                //Retrieve current information
                if (isset($id)) {
                    $this->_id = $id;
                    $this->getItemById($id);
                }

                $this->_img_path = DOCUMENT_ROOT."/../personalisation-images/";

            }
        }


        /**
         * Retrieves specified record ID from table
         * Populates object member elements
         *
         * @param $id
         */
        public function getItemById($id)
        {
            try {
                $stmt = $this->_dbconn->prepare("SELECT ID, MemberID, AES_DECRYPT(DelyFirstname, :key) AS DelyFirstname, AES_DECRYPT(DelySurname, :key) AS DelySurname, AES_DECRYPT(DelyOrganisation, :key) AS DelyOrganisation, AES_DECRYPT(DelyAddress1, :key) AS DelyAddress1, AES_DECRYPT(DelyAddress2, :key) AS DelyAddress2, AES_DECRYPT(DelyTown, :key) AS DelyTown, AES_DECRYPT(DelyCounty, :key) AS DelyCounty, AES_DECRYPT(DelyPostcode, :key) AS DelyPostcode, AES_DECRYPT(DelyCountry, :key) AS DelyCountry, AES_DECRYPT(DelyCountryCode, :key) AS DelyCountryCode, AES_DECRYPT(DelyEmail, :key) AS DelyEmail, AES_DECRYPT(DelyTelephone, :key) AS DelyTelephone, AES_DECRYPT(DelyMobile, :key) AS DelyMobile, AES_DECRYPT(InvFirstname, :key) AS InvFirstname, AES_DECRYPT(InvSurname, :key) AS InvSurname, AES_DECRYPT(InvOrganisation, :key) AS InvOrganisation, AES_DECRYPT(InvAddress1, :key) AS InvAddress1, AES_DECRYPT(InvAddress2, :key) AS InvAddress2, AES_DECRYPT(InvTown, :key) AS InvTown, AES_DECRYPT(InvCounty, :key) AS InvCounty, AES_DECRYPT(InvPostcode, :key) AS InvPostcode, AES_DECRYPT(InvCountry, :key) AS InvCountry, AES_DECRYPT(InvCountryCode, :key) AS InvCountryCode, AES_DECRYPT(InvEmail, :key) AS InvEmail, AES_DECRYPT(InvTelephone, :key) AS InvTelephone, AES_DECRYPT(InvMobile, :key) AS InvMobile, OrderDate, AES_DECRYPT(OrderNett, :key) AS OrderNett, AES_DECRYPT(OrderVAT, :key) AS OrderVAT, AES_DECRYPT(OrderGross, :key) AS OrderGross, AES_DECRYPT(VoucherCode, :key) AS VoucherCode, AES_DECRYPT(InvoiceNumber, :key) AS InvoiceNumber, InvoiceDate, AES_DECRYPT(PaymentMethod, :key) AS PaymentMethod, AES_DECRYPT(Paid, :key) AS Paid, DatePaid, AES_DECRYPT(Status, :key) AS Status, AES_DECRYPT(StatusDetail, :key) AS StatusDetail, AES_DECRYPT(Despatched, :key) AS Despatched, DespatchDate, AES_DECRYPT(IP, :key) AS IP, AES_DECRYPT(Stripe_PaymentIntentID, :key) AS Stripe_PaymentIntentID, AES_DECRYPT(Stripe_PaymentMethodID, :key) AS Stripe_PaymentMethodID, AES_DECRYPT(Stripe_Result, :key) AS Stripe_Result FROM Orders WHERE ID =  :id LIMIT 1");
                $stmt->execute([
                    'key' => AES_ENCRYPTION_KEY,
                    'id' => $id
                ]);
                $item = $stmt->fetch();
            } catch (Exception $e) {
                error_log("Failed to retrieve order details when searching by ID" . $e);
            }

            //Store details in relevant members
            $this->_id = $item['ID'];
            $this->_member_id = $item['MeetingID'];
            $this->_inv_firstname = $item['InvFirstname'];
            $this->_inv_surname = $item['InvSurname'];
            $this->_inv_organisation = $item['InvOrganisation'];
            $this->_inv_address1 = $item['InvAddress1'];
            $this->_inv_address2 = $item['InvAddress2'];
            $this->_inv_town = $item['InvTown'];
            $this->_inv_county = $item['InvCounty'];
            $this->_inv_postcode = $item['InvPostcode'];
            $this->_inv_country = $item['InvCountry'];
            $this->_inv_country_code = $item['InvCountryCode'];
            $this->_inv_email = $item['InvEmail'];
            $this->_inv_telephone = $item['InvTelephone'];
            $this->_inv_mobile = $item['InvMobile'];
            $this->_dely_firstname = $item['DelyFirstname'];
            $this->_dely_surname = $item['DelySurname'];
            $this->_dely_organisation = $item['DelyOrganisation'];
            $this->_dely_address1 = $item['DelyAddress1'];
            $this->_dely_address2 = $item['DelyAddress2'];
            $this->_dely_town = $item['DelyTown'];
            $this->_dely_county = $item['DelyCounty'];
            $this->_dely_postcode = $item['DelyPostcode'];
            $this->_dely_country = $item['DelyCountry'];
            $this->_dely_country_code = $item['DelyCountryCode'];
            $this->_dely_email = $item['DelyEmail'];
            $this->_dely_telephone = $item['DelyTelephone'];
            $this->_dely_mobile = $item['DelyMobile'];
            $this->_order_date = $item['OrderDate'];
            $this->_order_nett = $item['OrderNett'];
            $this->_order_vat = $item['OrderVAT'];
            $this->_order_gross = $item['OrderGross'];
            $this->_voucher_code = $item['VoucherCode'];
            $this->_invoice_number = $item['InvoiceNumber'];
            $this->_invoice_date = $item['InvoiceDate'];
            $this->_payment_method = $item['PaymentMethod'];
            $this->_paid = $item['Paid'];
            $this->_date_paid = $item['DatePaid'];
            $this->_status = $item['Status'];
            $this->_status_detail = $item['StatusDetail'];
            $this->_despatched = $item['Despatched'];
            $this->_despatch_date = $item['DespatchDate'];
            $this->_ip = $item['IP'];
            $this->_stripe_payment_intent_id = $item['Stripe_PaymentIntentID'];
            $this->_stripe_payment_method_id = $item['Stripe_PaymentMethodID'];
            $this->_stripe_result = $item['Stripe_Result'];

            //Order details
            $this->_order_details = $this->getOrderDetails();
            $item['OrderDetails'] = $this->_order_details;

            //Bring back the actual member details
            $MO = new Member();
            if (is_object($MO) && isset($this->_member_id) && $this->_member_id > 0) {
                $Member = $MO->getItemById($this->_member_id);
                if (is_array($Member) && count($Member) > 0) {
                    $item['MemberInfo'] = $Member;
                }
            }

            return $item;
        }


        public function getOrderByPaymentIntentID($piid) {
            if ($piid == '') { error_log('Order->getOrderByPaymentIntentID requires PaymentIntentID (from Stripe) to be passed'); return false; }

            //Retrieve
            $stmt = $this->_dbconn->prepare("SELECT ID FROM Orders WHERE CONVERT(AES_DECRYPT(Stripe_PaymentIntentID, :key) USING utf8) = :needle LIMIT 1");
            $result = $stmt->execute([
                'key' => AES_ENCRYPTION_KEY,
                'needle' => $piid
            ]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (is_array($order) && count($order) > 0) {
                return $this->getItemById($order['ID']);
            } else {
                return false;
            }
        }


        public function getOrderByPaymentMethodID($pmid) {
            if ($pmid == '') { error_log('Order->getOrderByPaymentMethodID requires PaymentMethodID (from Stripe) to be passed'); return false; }

            //Retrieve
            $stmt = $this->_dbconn->prepare("SELECT ID FROM Orders WHERE CONVERT(AES_DECRYPT(Stripe_PaymentMethodID, :key) USING utf8) = :needle LIMIT 1");
            $result = $stmt->execute([
                'key' => AES_ENCRYPTION_KEY,
                'needle' => $pmid
            ]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (is_array($order) && count($order) > 0) {
                return $this->getItemById($order['ID']);
            } else {
                return false;
            }
        }



        public function getOrderByOrderDetailID($detailid) {
            if (!is_numeric($detailid) || $detailid <= 0) { error_log('Order->getOrderByOrderDetailID requires order detail id to be passed'); return false; }

            //Retrieve
            $stmt = $this->_dbconn->prepare("SELECT OrderID FROM OrderDetails WHERE ID = :detailid ORDER BY ID ASC");
            $result = $stmt->execute([
                'detailid' => $detailid
            ]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (is_array($order) && count($order) > 0) {
                return $this->getItemById($order['OrderID']);
            } else {
                return false;
            }
        }


        /**
         * Saves the current object to the table in the database
         *
         * @throws Exception
         */
        public function saveItem()
        {
            //First need to determine if this is a new Abstract item
            if ($this->_id <= 0) {
                $this->createNewItem(); //_id should now be set
            }

            try {
                $stmt = $this->_dbconn->prepare("UPDATE Orders SET MemberID = :memberid, DelyFirstname = AES_ENCRYPT(:delyfirstname, :key),  DelySurname = AES_ENCRYPT(:delysurname, :key),  DelyOrganisation = AES_ENCRYPT(:delyorganisation, :key),  DelyAddress1 = AES_ENCRYPT(:delyaddress1, :key),  DelyAddress2 = AES_ENCRYPT(:delyaddress2, :key),  DelyTown = AES_ENCRYPT(:delytown, :key),  DelyCounty = AES_ENCRYPT(:delycounty, :key),  DelyPostcode = AES_ENCRYPT(:delypostcode, :key),  DelyCountry = AES_ENCRYPT(:delycountry, :key),  DelyCountryCode = AES_ENCRYPT(:delycountrycode, :key),  DelyEmail = AES_ENCRYPT(:delyemail, :key),  DelyTelephone = AES_ENCRYPT(:delytel, :key), DelyMobile = AES_ENCRYPT(:delymobile, :key), InvFirstname = AES_ENCRYPT(:invfirstname, :key),  InvSurname = AES_ENCRYPT(:invsurname, :key),  InvOrganisation = AES_ENCRYPT(:invorganisation, :key),  InvAddress1 = AES_ENCRYPT(:invaddress1, :key),  InvAddress2 = AES_ENCRYPT(:invaddress2, :key),  InvTown = AES_ENCRYPT(:invtown, :key),  InvCounty = AES_ENCRYPT(:invcounty, :key),  InvPostcode = AES_ENCRYPT(:invpostcode, :key),  InvCountry = AES_ENCRYPT(:invcountry, :key),  InvCountryCode = AES_ENCRYPT(:invcountrycode, :key),  InvEmail = AES_ENCRYPT(:invemail, :key),  InvTelephone = AES_ENCRYPT(:invtel, :key), InvMobile = AES_ENCRYPT(:invmobile, :key), OrderDate = :orderdate, OrderNett = AES_ENCRYPT(:ordernett, :key), OrderGross = AES_ENCRYPT(:ordergross, :key), VoucherCode = AES_ENCRYPT(:vouchercode, :key), InvoiceNumber = AES_ENCRYPT(:invnumber, :key), InvoiceDate = :invdate, PaymentMethod = AES_ENCRYPT(:paymentmethod, :key), Paid = AES_ENCRYPT(:paid, :key), DatePaid = :datepaid, Status = AES_ENCRYPT(:status, :key), StatusDetail = AES_ENCRYPT(:statusdetail, :key), Despatched = AES_ENCRYPT(:despatched, :key), DespatchDate = :despatchdate, IP = AES_ENCRYPT(:ip, :key), Stripe_PaymentIntentID = AES_ENCRYPT(:stripe_payment_intent_id, :key), Stripe_PaymentMethodID = AES_ENCRYPT(:stripe_payment_method_id, :key), Stripe_Result = AES_ENCRYPT(:stripe_result, :key) WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'key' => AES_ENCRYPTION_KEY,
                    'memberid' => $this->_member_id,
                    'delyfirstname' => $this->_dely_firstname,
                    'delysurname' => $this->_dely_surname,
                    'delyorganisation' => $this->_dely_organisation,
                    'delyaddress1' => $this->_dely_address1,
                    'delyaddress2' => $this->_dely_address2,
                    'delytown' => $this->_dely_town,
                    'delycounty' => $this->_dely_county,
                    'delypostcode' => $this->_dely_postcode,
                    'delycountry' => $this->_dely_country,
                    'delycountrycode' => $this->_dely_country_code,
                    'delyemail' => $this->_dely_email,
                    'delytel' => $this->_dely_telephone,
                    'delymobile' => $this->_dely_mobile,
                    'invfirstname' => $this->_inv_firstname,
                    'invsurname' => $this->_inv_surname,
                    'invorganisation' => $this->_inv_organisation,
                    'invaddress1' => $this->_inv_address1,
                    'invaddress2' => $this->_inv_address2,
                    'invtown' => $this->_inv_town,
                    'invcounty' => $this->_inv_county,
                    'invpostcode' => $this->_inv_postcode,
                    'invcountry' => $this->_inv_country,
                    'invcountrycode' => $this->_inv_country_code,
                    'invemail' => $this->_inv_email,
                    'invtel' => $this->_inv_telephone,
                    'invmobile' => $this->_inv_mobile,
                    'orderdate' => $this->_order_date,
                    'ordernett' => $this->_order_nett,
                    'ordervat' => $this->_order_vat,
                    'ordergross' => $this->_order_gross,
                    'vouchercode' => $this->_voucher_code,
                    'invnumber' => $this->_invoice_number,
                    'invdate' => $this->_invoice_date,
                    'paymentmethod' => $this->_payment_method,
                    'paid' => $this->_paid,
                    'datepaid' => $this->_date_paid,
                    'status' => $this->_status,
                    'statusdetail' => $this->_status_detail,
                    'despatched' => $this->_despatched,
                    'despatchdate' => $this->_despatch_date,
                    'ip' => $this->_ip,
                    'stripe_payment_intent_id' => $this->_stripe_payment_intent_id,
                    'stripe_payment_method_id' => $this->_stripe_payment_method_id,
                    'stripe_result' => $this->_stripe_result,
                    'id' => $this->_id
                ]);
                //print_r($stmt->errorInfo()[2]);
                if ($result === true) {
                    return true;
                } else {
                    error_log($stmt->errorInfo()[2]);
                    return false;
                }
            } catch (Exception $e) {
                error_log("Failed to save order record: " . $e);
            }
        }


        /**
         * Create new empty item
         *
         * Sets the _id property accordingly
         */
        public function createNewItem()
        {
            //Only execute if the _id property isn't already set
            if (is_numeric($this->_id) || $this->_id > 0) {
                throw new Exception('You cannot create a new item at this stage - the id property is already set as ' . $this->_id);
            }

            //Create DB item
            try {
                $result = $this->_dbconn->query("INSERT INTO Orders SET OrderDate = NOW()");
                $lastID = $this->_dbconn->lastInsertId();

                if ($lastID > 0) {
                    $this->_id = $lastID;
                }
            } catch (Exception $e) {
                error_log("Failed to create new order record: " . $e);
            }
        }

        /**
         * Returns array of complete database records for all items - as searched for
         *
         * @param string $needle     record id or string - default = empty
         * @param string $searchtype 'id','title','type' - default = title
         *
         * @return array
         */
        public function listAllItems($needle = '', $searchtype = null, $statusfilter = null, $dates = null)
        {
            $basesql = "SELECT ID FROM Orders ";

            $params = array();

            switch ($searchtype)
            {
                case 'id':
                    $search = " WHERE (ID = :needle)";
                    $order = " LIMIT 1";
                    $params['needle'] = $needle;
                    break;

                case 'memberid':
                    $search = " WHERE (MemberID = :needle)";
                    $order = " ORDER BY OrderDate DESC";
                    $params['needle'] = $needle;
                    break;

                case 'inv-number':
                    $search = " WHERE (CONVERT(AES_DECRYPT(InvNumber, :key) USING utf8) LIKE :needle)";
                    $order = " ORDER BY OrderDate DESC";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    $params['needle'] = $needle."%";
                    break;

                case 'surname':
                    $search = " WHERE (CONVERT(AES_DECRYPT(DelySurname, :key) USING utf8) LIKE :needle OR CONVERT(AES_DECRYPT(InvSurname, :key) USING utf8) LIKE :needle)";
                    $order = " ORDER BY OrderDate DESC";
                    $params['needle'] = $needle."%";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;

                case 'stripe-result':
                    $search = " WHERE (CONVERT(AES_DECRYPT(Stripe_Result, :key) USING utf8) = :needle)";
                    $order = " ORDER BY OrderDate DESC";
                    $params['needle'] = $needle;
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;

                case 'stripe-paymentintent':
                    $search = " WHERE (CONVERT(AES_DECRYPT(Stripe_PaymentIntentID, :key) USING utf8) LIKE :needle)";
                    $order = " ORDER BY OrderDate DESC";
                    $params['needle'] = $needle."%";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;

                case 'stripe-paymentmethod':
                    $search = " WHERE (CONVERT(AES_DECRYPT(Stripe_PaymentMethodID, :key) USING utf8) LIKE :needle)";
                    $order = " ORDER BY OrderDate DESC";
                    $params['needle'] = $needle."%";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;

                default:
                    $search = " WHERE (ID = ID)";
                    $order = " ORDER BY OrderDate DESC";
                    break;
            }

            //status filter
            if (is_array($statusfilter)) {
                $search .= " AND (";
                $n = 1;
                foreach($statusfilter as $filter) {
                    if ($n != 1 ) { $search .= " OR "; }
                    $search .= " CONVERT(AES_DECRYPT(Status, :key) USING utf8) = :filter" . $n;
                    $params['filter' . $n] = $filter;
                    $n++;
                }
                $search .= ") ";
            }

            //Any dates passed?
            if (is_array($dates) && count($dates) == 2) {
                $search .= " AND (OrderDate BETWEEN :date1 AND :date2) ";
                $params['date1'] = $dates[0];
                $params['date2'] = $dates[1];
            }



            //Create sql
            $sql = $basesql . $search . $order;

            /*echo $sql;
            print_r($params);
            */

            $items = array();
            try
            {
                $stmt = $this->_dbconn->prepare($sql);
                $stmt->execute($params);
                while ($item = $stmt->fetch(PDO::FETCH_ASSOC))
                {
                    $items[] = $this->getItemById($item['ID']);
                }
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Order items" . $e);
            }

            //return the array
            return $items;
        }



        /**
         * Delete the complete item - including any images
         *
         * @return mixed
         */
        public function deleteItem()
        {
            if (!is_numeric($this->_id)) {
                throw new Exception('Class Order requires the item ID to be set if you are trying to delete the item');
            }

            //We can only delete an order if it hasn't been Paid
            $Order = $this->getItemById($this->_id);
            if (is_array($Order) && count($Order) > 0 && $Order['Paid'] != 'Y') {
                //Delete all order detsails
                $stmt = $this->_dbconn->prepare("DELETE FROM OrderDetails WHERE OrderID = :id");
                $stmt->execute([
                    'id' => $this->_id
                ]);

                //Now delete the item from the DB
                try {
                    $stmt = $this->_dbconn->prepare("DELETE FROM Orders WHERE ID = :id LIMIT 1");
                    $result = $stmt->execute([
                        'id' => $this->_id
                    ]);
                } catch (Exception $e) {
                    error_log("Failed to delete order record: " . $e);
                }
            }

            if ($result === true) {
                //Unset the properties
                $this->_id = null;

                return true;
            } else {
                return false;
            }

        }


        public function getOrderDetails($orderid = null) {
            if (!is_numeric($orderid) || $orderid <= 0) { $orderid = $this->_id; }
            if (!is_numeric($orderid) || $orderid <= 0) { error_log('Order->getOrderDetails requires order id to be passed or set'); return false; }

            //Retrieve
            $stmt = $this->_dbconn->prepare("SELECT ID, OrderID, ProductID, AES_DECRYPT(ProductTitle, :key) AS ProductTitle, ItemQuantity, AES_DECRYPT(ItemPrice, :key) AS ItemPrice, AES_DECRYPT(ProductOption, :key) AS ProductOption, AES_DECRYPT(ProductPersonalisation, :key) AS ProductPersonalisation, AES_DECRYPT(ImgPersonalisation, :key) AS ImgPersonalisation, AES_DECRYPT(ImgPath, :key) AS ImgPath, AES_DECRYPT(ImgFilename, :key) AS ImgFilename, AES_DECRYPT(OptionalExtras, :key) AS OptionalExtras FROM OrderDetails WHERE OrderID = :orderid ORDER BY ID ASC");
            $result = $stmt->execute([
                'key' => AES_ENCRYPTION_KEY,
                'orderid' => $orderid
            ]);
            $orderlines = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->_order_details = $orderlines;

            return $orderlines;
        }


        public function addOrderLine($orderline, $orderid = null) {
            if (!is_numeric($orderid) || $orderid <= 0) { $orderid = $this->_id; }
            if (!is_numeric($orderid) || $orderid <= 0) { error_log('Order->addOrderLine requires order id to be passed or set'); return false; }
            if (!is_array($orderline) || count($orderline) <= 0) {
                error_log('Order->addOrderLine requires order line to be passed or set');
                return false;
            }

            //echo "adding order line: <br/>";
            //print_r($orderline);

            //Check array contains fields we need.
            $errors = array();
            //if ($orderline['ProductType'] == '') { $errors[] = "Product type is blank"; }
            //if (!is_numeric($orderline['ProductID']) || $orderline['ProductID'] <= 0) { $errors[] = "Product id is less than zero"; }
            if ($orderline['ProductTitle'] == '') { $errors[] = "Product title/description is blank"; }
            if (!is_numeric($orderline['ItemQuantity']) || $orderline['ItemQuantity'] <= 0) { $errors[] = "Item quantity is less than zero"; }
            //if (!is_numeric($orderline['ItemPrice']) || $orderline['ItemPrice'] <= 0) { $errors[] = "Item price is less than zero"; }
            //if ($orderline['ProductOption'] == '') { $errors[] = "Product option info is blank"; }
            //if ($orderline['ProductPersonalisation'] == '') { $errors[] = "Product personalisation info is blank"; }
            //if ($orderline['OptionalExtras'] == '') { $errors[] = "Product optional extras info is blank"; }

            if (count($errors) > 0) {
                return array('Success'=>false,'Errors'=>$errors);
            }


            //Insert
            $stmt = $this->_dbconn->prepare("INSERT INTO OrderDetails SET OrderID = :orderid, ProductID = :productid, ProductTitle = AES_ENCRYPT(:producttitle, :key), ItemQuantity = :itemquantity, ItemPrice = AES_ENCRYPT(:itemprice, :key), ProductOption = AES_ENCRYPT(:productoption, :key), ProductPersonalisation = AES_ENCRYPT(:productpersonalisation, :key), ImgPersonalisation = AES_ENCRYPT(:imgpersonalisation, :key), OptionalExtras = AES_ENCRYPT(:optionalextras, :key)");
            $result = $stmt->execute([
                'key' => AES_ENCRYPTION_KEY,
                'orderid' => $orderid,
                'productid' => $orderline['ProductID'],
                'producttitle' => $orderline['ProductTitle'],
                'itemquantity' => $orderline['ItemQuantity'],
                'itemprice' => $orderline['ItemPrice'],
                'productoption' => $orderline['ProductOption'],
                'productpersonalisation' => $orderline['Personalisation'],
                'imgpersonalisation' => $orderline['ImgPersonalisation'],
                'optionalextras' => $orderline['OptionalExtras']
            ]);

            /*echo "errors = ";
            print_r($stmt->errorInfo());
            print_r($stmt->errorInfo()[2]);
            echo "<br/><br/>";
            exit;*/

            return array('Success'=>$result, 'Errors'=>$stmt->errorInfo()[2]);
        }

        public function updateOrderLine($orderline, $orderid = null) {
            if (!is_numeric($orderid) || $orderid <= 0) { $orderid = $this->_id; }
            if (!is_numeric($orderid) || $orderid <= 0) { error_log('Order->updateOrderLine requires order id to be passed or set'); return false; }
            if (!is_array($orderline) || count($orderline) <= 0) {
                error_log('Order->updateOrderLine requires order line to be passed or set');
                return false;
            }

            //Check array contains fields we need.
            $errors = array();
            if ($orderline['ID'] == '') { $errors[] = "ID is blank"; }
            if (!is_numeric($orderline['ProductID']) || $orderline['ProductID'] <= 0) { $errors[] = "Product id is less than zero"; }
            if ($orderline['ProductTitle'] == '') { $errors[] = "Product title/description is blank"; }
            if (!is_numeric($orderline['ItemQuantity']) || $orderline['ItemQuantity'] <= 0) { $errors[] = "Item quantity is less than zero"; }
            if (!is_numeric($orderline['ItemPrice']) || $orderline['ItemPrice'] <= 0) { $errors[] = "Item price is less than zero"; }
            //if ($orderline['ProductOption'] == '') { $errors[] = "Product option info is blank"; }
            //if ($orderline['ProductPersonalisation'] == '') { $errors[] = "Product personalisation info is blank"; }
            //if ($orderline['OptionalExtras'] == '') { $errors[] = "Product optional extras info is blank"; }

            if (count($errors) > 0) {
                return array('Success'=>false,'Errors'=>$errors);
            }

            //Update
            $stmt = $this->_dbconn->prepare("UPDATE OrderDetails SET OrderID = :orderid, ProductID = :productid, ProductTitle = AES_ENCRYPT(:producttitle, :key), ItemQuantity = :itemquantity, ItemPrice = AES_ENCRYPT(:itemprice, :key), ProductOption = AES_ENCRYPT(:productoption, :key), ProductPersonalisation = AES_ENCRYPT(:productpersonalisation, :key), ImgPersonalisation = AES_ENCRYPT(:imgpersonalisation, :key), OptionalExtras = AES_ENCRYPT(:optionalextras, :key) WHERE ID = :id LIMIT 1");
            $result = $stmt->execute([
                'orderid' => $orderid,
                'productid' => $orderline['ProductID'],
                'producttitle' => $orderline['ProductTitle'],
                'itemquantity' => $orderline['ItemQuantity'],
                'itemprice' => $orderline['ItemPrice'],
                'productoption' => $orderline['ProductOption'],
                'productpersonalisation' => $orderline['ProductPersonalisation'],
                'imgpersonalisation' => $orderline['ImgPersonalisation'],
                'optionalextras' => $orderline['OptionalExtras'],
                'id' => $orderline['ID']
            ]);

            return array('Success'=>$result, 'Errors'=>$stmt->errorInfo()[2]);
        }


        public function deleteOrderLine($orderlineid) {
            if (!is_numeric($orderlineid) || $orderlineid <= 0) { error_log('Order->deleteOrderLine requires order line id to be passed'); return false; }

            //Delete
            $stmt = $this->_dbconn->prepare("DELETE FROM OrderDetails WHERE ID = :id");
            $result = $stmt->execute([
                'id' => $orderlineid
            ]);
            $errors = $stmt->errorInfo()[2];

            return true;
        }



        public function updateOrderLineImage($OrderLineID, $ImgFilename)
        {
            if (!is_numeric($OrderLineID) || $OrderLineID <= 0) {
                error_log('Order->updateOrderLineImage requires order line id to be passed');
                return false;
            }
            if ($ImgFilename == '') {
                error_log('Order->updateOrderLineImage requires Image Filename to be passed');
                return false;
            }

            //Update
            $stmt = $this->_dbconn->prepare("UPDATE OrderDetails SET ImgFilename = AES_ENCRYPT(:imgfilename, :key), ImgPath = AES_ENCRYPT(:imgpath, :key) WHERE ID = :id LIMIT 1");
            $result = $stmt->execute([
                'key' => AES_ENCRYPTION_KEY,
                'imgfilename' => $ImgFilename,
                'imgpath' => $this->_img_path,
                'id' => $OrderLineID
            ]);
            $errors = $stmt->errorInfo()[2];

            return array('Success' => $result, 'Errors' => $errors);
        }


        public function deleteOrderLineImage($OrderLineID)
        {
            if (!is_numeric($OrderLineID) || $OrderLineID <= 0) {
                error_log('Order->deleteOrderLineImage requires order line id to be passed');
                return false;
            }

            //Delete files
            //remove the file
            foreach( glob($this->_img_path.$OrderLineID."_*") as $file )
            {
                unlink($file);
            }
            //Update
            $stmt = $this->_dbconn->prepare("UPDATE OrderDetails SET ImgFilename = null, ImgPath = null WHERE ID = :id LIMIT 1");
            $result = $stmt->execute([
                'id' => $OrderLineID
            ]);
            $errors = $stmt->errorInfo()[2];

            return array('Success' => $result, 'Errors' => $errors);
        }

        public function deleteAllOrderLineImages() {
            if (isset($this->_order_details) && is_array($this->_order_details)) {
                foreach ($this->_order_details as $line) {
                    $this->deleteOrderLineImage($line['ID']);
                }
            }
        }


        /**
         * Retrieve the next invoice number
         */
        public function getNextInvoiceNumber() {
            try {
                $stmt = $this->_dbconn->prepare("SELECT NextNumber FROM NextInvoice WHERE ID = 1");
                $result = $stmt->execute();
                $row = $stmt->fetch();
                $nextnumber = $row['NextNumber'];
                if ($nextnumber > 0) {
                    $nextnextnumber = $nextnumber + 1;
                    $stmt = $this->_dbconn->prepare("UPDATE NextInvoice SET NextNumber = :nextnumber WHERE ID = 1");
                    $result = $stmt->execute([
                        'nextnumber' => $nextnextnumber
                    ]);
                    return $nextnumber;
                }
            } catch (Exception $e) {
                error_log("Failed to delete order record: " . $e);
            }
            return false;
        }


        /**
         * Output order details for use in email/order summary pages
         */
        public function outputOrderDetails($OrderDetails = null, $ShowImages = true, $AdminMode = false) {
            if (!is_array($OrderDetails) || count($OrderDetails) <= 0) {
                $OrderDetails = $this->_order_details;
            }
            if (!is_array($OrderDetails) || count($OrderDetails) <= 0) {
                error_log('Order->outputOrderDetails() requires order details to be set or passed');
                return false;
            }

            $output = "<table class='cart'><tr>";
            if ($ShowImages == true) {
                $output .= "<th></th>";
            }
            $output .= "<th>Item</th><th>Price</th></tr>";

            $PO = new Product();
            if (!is_object($PO)) {
                return false;
            }

            foreach ($OrderDetails as $Line) {
                $Product = $PO->getItemById($Line['ProductID']);

                $output .= "<tr>";
                if ($ShowImages == true) {
                    //Image
                    if ($Product['ImgFilename'] != '' && file_exists(FixOutput(DOCUMENT_ROOT . $Product['ImgPath'] . "small/" . $Product['ImgFilename']))) {
                        $img = "https://".SITEFQDN.FixOutput($Product['ImgPath'] . "small/" . $Product['ImgFilename']);
                    } else {
                        $img = "https://".SITEFQDN."/assets/img/placeholder.png";
                    }
                    $output .= "<td style='width: 80px;'><img src='" . $img . "' alt='" . FixOutput($Line['ProductTitle']) . "' style='width:80px; height: auto;'/></td>";
                }
                $output .= "<td><strong>" . $Line['ProductTitle'] . "</strong><br/>";
                $output .= "Qty: 1";
                if ($Line['ProductOption'] != '') { $output .= "<br/>Option: ".$Line['ProductOption']; }
                if ($Line['ProductPersonalisation'] != '') { $output .= "<br/>Personalisation: <em>".$Line['ProductPersonalisation']."</em>"; }
                /*if (is_array($Line['OptionalExtras']) && count($Line['OptionalExtras']) > 0) {
                    $output .= "<br/>Extras: ";
                    foreach ($Line['OptionalExtras'] as $extra) {
                        $output .= $extra . "<br/>";
                    }
                }*/
                if ($Line['OptionalExtras'] != '') { $output .= "<br/>Extras: ".nl2br($Line['OptionalExtras']); }

                //Admin mode - show personalisation
                if ($AdminMode == true) {
                    if ($Line['ImgPersonalisation'] == true && $Line['ImgFilename'] != '') {
                        //Show image
                        $output .= "<div class='image-personalisation'>";
                        $output .= "<br/><img src='data:image/jpeg;base64,".$this->getPersonalisationImage($Line['ImgPath'].$Line['ImgFilename'])."' alt='Personalisation image' class='personalisation-image' /><br/>";
                        $output .= "<a href='/admin/order_download_personalisation_image.php?orderdetailid=".$Line['ID']."' target='_blank'>Download this image</a>";
                        $output .= "</div>";
                    } elseif ($Line['ImgPersonalisation'] == true && $Line['ImgFilename'] == '') {
                        $output .= "<br/><strong>PERSONALISATION IMAGE NOT UPLOADED - CONTACT THE CUSTOMER</strong>";
                    }
                }
                $output .= "</td>";
                $output .= "<td>";
                if ($Line['ItemPrice'] > 0) {
                    $output .= "&pound;".number_format($Line['ItemPrice'],2);
                }
                $output .= "</td>";
                $output .= "</tr>"; //end of row
            }


            //Gross/Total
            $output .= "<tr>";
            if ($ShowImages == true) {
                $output .= "<td></td>";
            }
            $output .= "<td>Order Total:</td>";
            $output .= "<td>&pound;".number_format($this->_order_gross,2)."</td>";
            $output .= "</tr>"; // end of row

            $output .= "</table>";

            return $output;
        }



        public function getPersonalisationImage($pathandfilename) {
            $imgBinary = fread(fopen($pathandfilename, "r"), filesize($pathandfilename));

            return base64_encode($imgBinary);
        }



        ###########################################################
        # Getters and Setters
        ###########################################################

        /**
         * @return int|string
         */
        public function getID()
        {
            return $this->_id;
        }

        /**
         * @param $id
         */
        public function setID($id)
        {
            $this->_id = $id;
        }

        /**
         * @return mixed
         */
        public function getMemberId()
        {
            return $this->_member_id;
        }

        /**
         * @param mixed $delegate_id
         */
        public function setMemberId($delegate_id)
        {
            $this->_member_id = $delegate_id;
        }

        /**
         * @return mixed
         */
        public function getDelyFirstname()
        {
            return $this->_dely_firstname;
        }

        /**
         * @param mixed $dely_firstname
         */
        public function setDelyFirstname($dely_firstname)
        {
            $this->_dely_firstname = $dely_firstname;
        }

        /**
         * @return mixed
         */
        public function getDelySurname()
        {
            return $this->_dely_surname;
        }

        /**
         * @param mixed $dely_surname
         */
        public function setDelySurname($dely_surname)
        {
            $this->_dely_surname = $dely_surname;
        }

        /**
         * @return mixed
         */
        public function getDelyOrganisation()
        {
            return $this->_dely_organisation;
        }

        /**
         * @param mixed $dely_organisation
         */
        public function setDelyOrganisation($dely_organisation)
        {
            $this->_dely_organisation = $dely_organisation;
        }

        /**
         * @return mixed
         */
        public function getDelyAddress1()
        {
            return $this->_dely_address1;
        }

        /**
         * @param mixed $dely_address1
         */
        public function setDelyAddress1($dely_address1)
        {
            $this->_dely_address1 = $dely_address1;
        }

        /**
         * @return mixed
         */
        public function getDelyAddress2()
        {
            return $this->_dely_address2;
        }

        /**
         * @param mixed $dely_address2
         */
        public function setDelyAddress2($dely_address2)
        {
            $this->_dely_address2 = $dely_address2;
        }

        /**
         * @return mixed
         */
        public function getDelyTown()
        {
            return $this->_dely_town;
        }

        /**
         * @param mixed $dely_town
         */
        public function setDelyTown($dely_town)
        {
            $this->_dely_town = $dely_town;
        }

        /**
         * @return mixed
         */
        public function getDelyCounty()
        {
            return $this->_dely_county;
        }

        /**
         * @param mixed $dely_county
         */
        public function setDelyCounty($dely_county)
        {
            $this->_dely_county = $dely_county;
        }

        /**
         * @return mixed
         */
        public function getDelyPostcode()
        {
            return $this->_dely_postcode;
        }

        /**
         * @param mixed $dely_postcode
         */
        public function setDelyPostcode($dely_postcode)
        {
            $this->_dely_postcode = $dely_postcode;
        }

        /**
         * @return mixed
         */
        public function getDelyCountry()
        {
            return $this->_dely_country;
        }

        /**
         * @param mixed $dely_country
         */
        public function setDelyCountry($dely_country)
        {
            $this->_dely_country = $dely_country;
        }

        /**
         * @return mixed
         */
        public function getDelyCountryCode()
        {
            return $this->_dely_country_code;
        }

        /**
         * @param mixed $dely_country_code
         */
        public function setDelyCountryCode($dely_country_code)
        {
            $this->_dely_country_code = $dely_country_code;
        }

        /**
         * @return mixed
         */
        public function getDelyEmail()
        {
            return $this->_dely_email;
        }

        /**
         * @param mixed $dely_email
         */
        public function setDelyEmail($dely_email)
        {
            $this->_dely_email = $dely_email;
        }

        /**
         * @return mixed
         */
        public function getDelyTelephone()
        {
            return $this->_dely_telephone;
        }

        /**
         * @param mixed $dely_telephone
         */
        public function setDelyTelephone($dely_telephone)
        {
            $this->_dely_telephone = $dely_telephone;
        }

        /**
         * @return mixed
         */
        public function getDelyMobile()
        {
            return $this->_dely_mobile;
        }

        /**
         * @param mixed $dely_mobile
         */
        public function setDelyMobile($dely_mobile)
        {
            $this->_dely_mobile = $dely_mobile;
        }

        /**
         * @return mixed
         */
        public function getInvFirstname()
        {
            return $this->_inv_firstname;
        }

        /**
         * @param mixed $inv_firstname
         */
        public function setInvFirstname($inv_firstname)
        {
            $this->_inv_firstname = $inv_firstname;
        }

        /**
         * @return mixed
         */
        public function getInvSurname()
        {
            return $this->_inv_surname;
        }

        /**
         * @param mixed $inv_surname
         */
        public function setInvSurname($inv_surname)
        {
            $this->_inv_surname = $inv_surname;
        }

        /**
         * @return mixed
         */
        public function getInvOrganisation()
        {
            return $this->_inv_organisation;
        }

        /**
         * @param mixed $inv_organisation
         */
        public function setInvOrganisation($inv_organisation)
        {
            $this->_inv_organisation = $inv_organisation;
        }

        /**
         * @return mixed
         */
        public function getInvAddress1()
        {
            return $this->_inv_address1;
        }

        /**
         * @param mixed $inv_address1
         */
        public function setInvAddress1($inv_address1)
        {
            $this->_inv_address1 = $inv_address1;
        }

        /**
         * @return mixed
         */
        public function getInvAddress2()
        {
            return $this->_inv_address2;
        }

        /**
         * @param mixed $inv_address2
         */
        public function setInvAddress2($inv_address2)
        {
            $this->_inv_address2 = $inv_address2;
        }

        /**
         * @return mixed
         */
        public function getInvTown()
        {
            return $this->_inv_town;
        }

        /**
         * @param mixed $inv_town
         */
        public function setInvTown($inv_town)
        {
            $this->_inv_town = $inv_town;
        }

        /**
         * @return mixed
         */
        public function getInvCounty()
        {
            return $this->_inv_county;
        }

        /**
         * @param mixed $inv_county
         */
        public function setInvCounty($inv_county)
        {
            $this->_inv_county = $inv_county;
        }

        /**
         * @return mixed
         */
        public function getInvPostcode()
        {
            return $this->_inv_postcode;
        }

        /**
         * @param mixed $inv_postcode
         */
        public function setInvPostcode($inv_postcode)
        {
            $this->_inv_postcode = $inv_postcode;
        }

        /**
         * @return mixed
         */
        public function getInvCountry()
        {
            return $this->_inv_country;
        }

        /**
         * @param mixed $inv_country
         */
        public function setInvCountry($inv_country)
        {
            $this->_inv_country = $inv_country;
        }

        /**
         * @return mixed
         */
        public function getInvCountryCode()
        {
            return $this->_inv_country_code;
        }

        /**
         * @param mixed $inv_country_code
         */
        public function setInvCountryCode($inv_country_code)
        {
            $this->_inv_country_code = $inv_country_code;
        }

        /**
         * @return mixed
         */
        public function getInvEmail()
        {
            return $this->_inv_email;
        }

        /**
         * @param mixed $inv_email
         */
        public function setInvEmail($inv_email)
        {
            $this->_inv_email = $inv_email;
        }

        /**
         * @return mixed
         */
        public function getInvTelephone()
        {
            return $this->_inv_telephone;
        }

        /**
         * @param mixed $inv_telephone
         */
        public function setInvTelephone($inv_telephone)
        {
            $this->_inv_telephone = $inv_telephone;
        }

        /**
         * @return mixed
         */
        public function getInvMobile()
        {
            return $this->_inv_mobile;
        }

        /**
         * @param mixed $inv_mobile
         */
        public function setInvMobile($inv_mobile)
        {
            $this->_inv_mobile = $inv_mobile;
        }

        /**
         * @return mixed
         */
        public function getOrderDate()
        {
            return $this->_order_date;
        }

        /**
         * @param mixed $order_date
         */
        public function setOrderDate($order_date)
        {
            if ($order_date == '' || $order_date == '0000-00-00' || $order_date == '0000-00-00 00:00:00') { $order_date = null; }
            $this->_order_date = $order_date;
        }

        /**
         * @return mixed
         */
        public function getOrderGross()
        {
            return $this->_order_gross;
        }

        /**
         * @param mixed $order_gross
         */
        public function setOrderGross($order_gross)
        {
            $this->_order_gross = $order_gross;
        }

        /**
         * @return mixed
         */
        public function getOrderVat()
        {
            return $this->_order_vat;
        }

        /**
         * @param mixed $order_vat
         */
        public function setOrderVat($order_vat)
        {
            $this->_order_vat = $order_vat;
        }

        /**
         * @return mixed
         */
        public function getOrderNett()
        {
            return $this->_order_nett;
        }

        /**
         * @param mixed $order_nett
         */
        public function setOrderNett($order_nett)
        {
            $this->_order_nett = $order_nett;
        }

        /**
         * @return mixed
         */
        public function getVoucherCode()
        {
            return $this->_voucher_code;
        }

        /**
         * @param mixed $voucher_code
         */
        public function setVoucherCode($voucher_code)
        {
            $this->_voucher_code = $voucher_code;
        }

        /**
         * @return mixed
         */
        public function getInvoiceNumber()
        {
            return $this->_invoice_number;
        }

        /**
         * @param mixed $invoice_number
         */
        public function setInvoiceNumber($invoice_number)
        {
            $this->_invoice_number = $invoice_number;
        }

        /**
         * @return mixed
         */
        public function getInvoiceDate()
        {
            return $this->_invoice_date;
        }

        /**
         * @param mixed $invoice_date
         */
        public function setInvoiceDate($invoice_date)
        {
            if ($invoice_date == '' || $invoice_date == '0000-00-00' || $invoice_date == '0000-00-00 00:00:00') { $invoice_date = null; }
            $this->_invoice_date = $invoice_date;
        }

        /**
         * @return mixed
         */
        public function getPaymentMethod()
        {
            return $this->_payment_method;
        }

        /**
         * @param mixed $payment_method
         */
        public function setPaymentMethod($payment_method)
        {
            $this->_payment_method = $payment_method;
        }

        /**
         * @return mixed
         */
        public function getPaid()
        {
            return $this->_paid;
        }

        /**
         * @param mixed $paid
         */
        public function setPaid($paid)
        {
            $this->_paid = $paid;
        }

        /**
         * @return mixed
         */
        public function getDatePaid()
        {
            return $this->_date_paid;
        }

        /**
         * @param mixed $date_paid
         */
        public function setDatePaid($date_paid)
        {
            if ($date_paid == '' || $date_paid == '0000-00-00' || $date_paid == '0000-00-00 00:00:00') { $date_paid = null; }
            $this->_date_paid = $date_paid;
        }

        /**
         * @return mixed
         */
        public function getStatus()
        {
            return $this->_status;
        }

        /**
         * @param mixed $status
         */
        public function setStatus($status)
        {
            $this->_status = $status;
        }

        /**
         * @return mixed
         */
        public function getStatusDetail()
        {
            return $this->_status_detail;
        }

        /**
         * @param mixed $status_detail
         */
        public function setStatusDetail($status_detail)
        {
            $this->_status_detail = $status_detail;
        }

        /**
         * @return mixed
         */
        public function getDespatched()
        {
            return $this->_despatched;
        }

        /**
         * @param mixed $despatched
         */
        public function setDespatched($despatched)
        {
            $this->_despatched = $despatched;
        }

        /**
         * @return mixed
         */
        public function getDespatchDate()
        {
            return $this->_despatch_date;
        }

        /**
         * @param mixed $despatch_date
         */
        public function setDespatchDate($despatch_date)
        {
            if ($despatch_date == '' || $despatch_date == '0000-00-00' || $despatch_date == '0000-00-00 00:00:00') { $despatch_date = null; }
            $this->_despatch_date = $despatch_date;
        }

        /**
         * @return mixed
         */
        public function getIp()
        {
            return $this->_ip;
        }

        /**
         * @param mixed $ip
         */
        public function setIp($ip)
        {
            $this->_ip = $ip;
        }


        /**
         * @return string
         */
        public function getImgPath()
        {
            return $this->_img_path;
        }

        /**
         * @param string $img_path
         */
        public function setImgPath($img_path)
        {
            $this->_img_path = $img_path;
        }

        /**
         * @return mixed
         */
        public function getStripePaymentIntentId()
        {
            return $this->_stripe_payment_intent_id;
        }

        /**
         * @param mixed $stripe_payment_intent_id
         */
        public function setStripePaymentIntentId($stripe_payment_intent_id): void
        {
            $this->_stripe_payment_intent_id = $stripe_payment_intent_id;
        }

        /**
         * @return mixed
         */
        public function getStripeResult()
        {
            return $this->_stripe_result;
        }

        /**
         * @param mixed $stripe_result
         */
        public function setStripeResult($stripe_result): void
        {
            $this->_stripe_result = $stripe_result;
        }

        /**
         * @return mixed
         */
        public function getStripePaymentMethodId()
        {
            return $this->_stripe_payment_method_id;
        }

        /**
         * @param mixed $stripe_payment_method_id
         */
        public function setStripePaymentMethodId($stripe_payment_method_id): void
        {
            $this->_stripe_payment_method_id = $stripe_payment_method_id;
        }






    }