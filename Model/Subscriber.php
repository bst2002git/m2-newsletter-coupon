<?php
namespace Howard\NewsletterCoupon\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;


class Subscriber extends \Magento\Newsletter\Model\Subscriber
{

    /**
     * Retrieve the coupon code
     *
     * @return string
     */
     public function __construct(
         \Magento\Framework\Model\Context $context,
         \Magento\Framework\Registry $registry,
         \Magento\Newsletter\Helper\Data $newsletterData,
         \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
         \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
         \Magento\Store\Model\StoreManagerInterface $storeManager,
         \Magento\Customer\Model\Session $customerSession,
         CustomerRepositoryInterface $customerRepository,
         AccountManagementInterface $customerAccountManagement,
         \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
         \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
         \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
         array $data = [],
         \Magento\Framework\Stdlib\DateTime\DateTime $dateTime = null,
         CustomerInterfaceFactory $customerFactory = null,
         DataObjectHelper $dataObjectHelper = null,
         \Magento\SalesRule\Api\RuleRepositoryInterface $ruleRepository,
         \Magento\SalesRule\Model\CouponGenerator $couponGenerator,
         \Howard\NewsletterCoupon\Helper\Config $config
     ) {
         $this->_ruleRepository = $ruleRepository;
         $this->_couponGenerator = $couponGenerator;
         $this->config = $config;

         # parent constructor
         $this->_newsletterData = $newsletterData;
         $this->_scopeConfig = $scopeConfig;
         $this->_transportBuilder = $transportBuilder;
         $this->_storeManager = $storeManager;
         $this->_customerSession = $customerSession;
         $this->dateTime = $dateTime ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
             \Magento\Framework\Stdlib\DateTime\DateTime::class
         );
         $this->customerFactory = $customerFactory ?: ObjectManager::getInstance()
             ->get(CustomerInterfaceFactory::class);
         $this->dataObjectHelper = $dataObjectHelper ?: ObjectManager::getInstance()
             ->get(DataObjectHelper::class);
         $this->customerRepository = $customerRepository;
         $this->customerAccountManagement = $customerAccountManagement;
         $this->inlineTranslation = $inlineTranslation;
         parent::__construct($context, $registry, $newsletterData, $scopeConfig, $transportBuilder, $storeManager, $customerSession, $customerRepository,
         $customerAccountManagement, $inlineTranslation,$resource, $resourceCollection, $data, $dateTime, $customerFactory, $dataObjectHelper);
     }


      public function sendConfirmationSuccessEmail()
      {
        if (!$this->config->isEnabled()) {

          parent::sendConfirmationSuccessEmail();

        }else{
          if ($this->getImportMode()) {
              return $this;
          }

          if (!$this->_scopeConfig->getValue(
              self::XML_PATH_SUCCESS_EMAIL_TEMPLATE,
              \Magento\Store\Model\ScopeInterface::SCOPE_STORE
          ) || !$this->_scopeConfig->getValue(
              self::XML_PATH_SUCCESS_EMAIL_IDENTITY,
              \Magento\Store\Model\ScopeInterface::SCOPE_STORE
          )
          ) {
              return $this;
          }

          $this->inlineTranslation->suspend();

          $this->_transportBuilder->setTemplateIdentifier(
              $this->_scopeConfig->getValue(
                  self::XML_PATH_SUCCESS_EMAIL_TEMPLATE,
                  \Magento\Store\Model\ScopeInterface::SCOPE_STORE
              )
          )->setTemplateOptions(
              [
                  'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                  'store' => $this->_storeManager->getStore()->getId(),
              ]
          )->setTemplateVars(
              [
                  'subscriber' => $this,
                  'coupon_code' => $this->generateCouponCode($this->config->ruleId())
              ]
          )->setFrom(
              $this->_scopeConfig->getValue(
                  self::XML_PATH_SUCCESS_EMAIL_IDENTITY,
                  \Magento\Store\Model\ScopeInterface::SCOPE_STORE
              )
          )->addTo(
              $this->getEmail(),
              $this->getName()
          );
          $transport = $this->_transportBuilder->getTransport();
          $transport->sendMessage();

          $this->inlineTranslation->resume();

          return $this;
        }
      }




    protected function generateCouponCode($rule_id)
    {
        $rule = $this->_ruleRepository->getById($rule_id);


        $data = array(
            'rule_id' => $rule->getRuleId(),
            'qty' => '1',
            'length' => '6',
            'format' => 'alphanum',
            'prefix' => 'HOWARD',
            'suffix' => 'YANG',
        );
        return $this->_couponGenerator->generateCodes($data)[0];

      }
}
    //
    //
    //
    //
    //
    //     try {
    //         $couponData = [];
    //         $couponData['name'] = '$5 Gift Voucher Newsletter Subscription ('.$this->getEmail().')';
    //         $couponData['is_active'] = '1';
    //         $couponData['simple_action'] = 'by_fixed';
    //         $couponData['discount_amount'] = '5';
    //         $couponData['from_date'] = date('Y-m-d');
    //         $couponData['to_date'] = '2019-12-31 23:59:59';
    //         $couponData['uses_per_coupon'] = '1';
    //         $couponData['coupon_type'] = '2';
    //         $couponData['customer_group_ids'] = $this->getCustomerGroupIds();
    //         $couponData['website_ids'] = $this->getWebsiteIds();
    //         /** @var \Magento\SalesRule\Model\Rule $rule */
    //         $rule = $this->_getSalesRule();
    //         $couponCode = $rule->getCouponCodeGenerator()->setLength(4)->setAlphabet(
    //             'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
    //         )->generateCode().'SUBNEW';
    //         $couponData['coupon_code'] = $couponCode;
    //         $rule->loadPost($couponData);
    //         $rule->save();
    //         return $couponCode;
    //     } catch (\Exception $e) {
    //         return null;
    //     }
    // }
    //
    // /**
    //  * Retrieve the customer group ids
    //  *
    //  * @return array
    //  */
    // protected function getCustomerGroupIds()
    // {
    //     $groupsIds = [];
    //     $collection = $this->_getCustomerGroup()->getCollection();
    //     foreach ($collection as $group) {
    //         $groupsIds[] = $group->getId();
    //     }
    //     return $groupsIds;
    // }
    //
    // /**
    //  * Retrieve the website ids
    //  *
    //  * @return array
    //  */
    // protected function getWebsiteIds()
    // {
    //     $websiteIds = [];
    //     $collection = $this->_getWebsite()->getCollection();
    //     foreach ($collection as $website) {
    //         $websiteIds[] = $website->getId();
    //     }
    //     return $websiteIds;
    // }
    //
    // /**
    //  * @return \Magento\Customer\Model\Group
    //  */
    // protected function _getCustomerGroup()
    // {
    //     if ($this->_customerGroup === null) {
    //         $this->_customerGroup = ObjectManager::getInstance()->get(\Magento\Customer\Model\Group::class);
    //     }
    //     return $this->_customerGroup;
    // }
    //
    // /**
    //  * @return \Magento\Store\Model\Website
    //  */
    // protected function _getWebsite()
    // {
    //     if ($this->_website === null) {
    //         $this->_website = ObjectManager::getInstance()->get(\Magento\Store\Model\Website::class);
    //     }
    //     return $this->_website;
    // }
    //
    // /**
    //  * @return \Magento\SalesRule\Model\Rule
    //  */
    // protected function _getSalesRule()
    // {
    //     if ($this->_salesRule === null) {
    //         $this->_salesRule = ObjectManager::getInstance()->get(\Magento\SalesRule\Model\Rule::class);
    //     }
    //     return $this->_salesRule;
    // }
