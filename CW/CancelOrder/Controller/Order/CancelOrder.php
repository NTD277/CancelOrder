<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CW\CancelOrder\Controller\Order;

class CancelOrder extends \Magento\Framework\App\Action\Action 
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $order;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Service\OrderService
     */
    protected $orderService;

    /**
     * @var \Magento\Sales\Api\OrderManagementInterface
     */
    protected $orderManagement;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $sessionCustomer;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Sales\Model\OrderFactory $order
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Sales\Api\OrderManagementInterface $orderManagement
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Customer\Model\Session $sessionCustomer
     * @param \Magento\Sales\Model\Service\OrderService $orderService
     * 
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Sales\Model\OrderFactory $order,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Customer\Model\Session $sessionCustomer,
        \Magento\Sales\Model\Service\OrderService $orderService
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->order = $order;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderManagement = $orderManagement;
        $this->orderService = $orderService;
        $this->orderRepository = $orderRepository;
        $this->sessionCustomer = $sessionCustomer;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $orderid = $this->getRequest()->getParam('order_id', false);
        $dataOrder = $this->checkOrderExistById($orderid);
        if (!$dataOrder) {
            $this->messageManager->addError(__('This order is not exist'));
        } else {
            $order = $this->orderRepository->get($orderid);
            $checkIsYourOrder = $this->checkIsYourOrder($order);
            $checkStatusOrder = $this->checkStatusOrder($order);
            try {
                if (!$checkIsYourOrder) {
                    $this->messageManager->addError(__('This order is not yours'));
                } elseif ($checkStatusOrder !== 'pending') {
                    $this->messageManager->addError(__("Your order has status '$checkStatusOrder'. You can not cancel this order"));
                } else {
                    $order->setStatus('canceled');
                    $order->save();
                    $this->messageManager->addSuccess(__('Cancel order successfully'));
                }
            } catch (\Exception $other) {
                $this->messageManager->addException($other, __('We can\'t cancel the order right now.'));
            }
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/history');
    }

    /**
     * Check order exist by orderid
     *
     * @param $orderid
     * @return array
     */
    private function checkOrderExistById($orderid)
    {
        $collection = $this->orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', $orderid);
        $data = $collection->getData();
        return $data;
    }

    /**
     * Check is your order
     *
     * @param $order
     * @return true
     */
    private function checkIsYourOrder($order)
    {
        $customerData = $this->sessionCustomer->getCustomer();
        $nameCustomerOrder = $order->getCustomerName();
        $nameCustomerSesstion = $customerData->getName();
        if ($nameCustomerOrder !== $nameCustomerSesstion) {
            return false;
        }
        return true;
    }

    /**
     * Check status order
     *
     * @param $order
     * @return string
     */
    private function checkStatusOrder($order)
    {
        $statusOrderNow = $order->getStatus();
        return $statusOrderNow;
    }
}
