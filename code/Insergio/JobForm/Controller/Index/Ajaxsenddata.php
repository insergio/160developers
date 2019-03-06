<?php

namespace Insergio\JobForm\Controller\Index;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

class Ajaxsenddata extends \Magento\Framework\App\Action\Action{

    protected $_resultPageFactory;
    protected $_resource;
    protected $_transportBuilder;
    protected $_mediaDirectory;
    protected $_fileUploaderFactory;


    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->_transportBuilder    = $transportBuilder;
        $this->_resultPageFactory   = $resultJsonFactory;
        $this->_resource            = $resource;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        parent::__construct($context);
    }
    /**
     * Index action
     *
     * @return $this
     */
    public function execute(){
        $post           = $this->getRequest()->getPostValue();
        $objectManager  = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $storeManager   = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        if(isset($post) && is_array($post) && count($post)>0){
            $store          = $storeManager->getStore()->getId();
            $url            = $storeManager->getStore()->getBaseUrl();
            $formtype=$post['formtype'];
            
            switch($formtype){
                case 1:
                $nombre         = $post['nombre'];
                $celular      = $post['celular'];
                $linkedin            = $post['linkedin'];
                $area            = $post['area'];
                $email          = (isset($post['email']))?trim($post['email']):'';
                break;

                case 2:
                $place         = $post['place'];
                $time      = $post['time'];
                $travel            = $post['travel'];
                $move            = $post['move'];
                $years            = $post['years'];
                $email = 'contacto@160devs.com';
                $nombre = 'contacto';
                break;

                case 3:
                $nombre         = $post['nombre'];
                $celular      = $post['celular'];
                $area            = $post['area'];
                $email          = (isset($post['email']))?trim($post['email']):'';
                $nombre2         = $post['nombre2'];
                $celular2      = $post['celular2'];
                $email2          = (isset($post['email2']))?trim($post['email2']):'';

                break;
            }

            $scopeConfig    = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
            $emailSender    = $scopeConfig->getValue('customer/formtrabajo/formemail', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $files          = $this->getRequest()->getFiles('upload_document');
            $backUrl        = $post['urlformcontacus'];

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $url = $backUrl;
                $this->messageManager->addError(__("El email no es valido"));
            }else{
                if(!empty($emailSender) && filter_var($emailSender, FILTER_VALIDATE_EMAIL)){
                    try{
                        switch($formtype){
                            case 1:
                            $transport = $this->_transportBuilder->setTemplateIdentifier('template_trabaja')
                            ->setTemplateOptions(['area' => 'frontend', 'store' => $store])
                            ->setTemplateVars(
                                [
                                    'store'     => $storeManager->getStore(),
                                    'nombre'    => $nombre,
                                    'celular' => $celular,
                                    'email'     => $email,
                                    'linkedin'       => $linkedin,
                                    'area'      => $area
                                ]
                            )
                            ->setFrom('general')
                            ->addTo($emailSender, $nombre)
                            ->getTransport();
                            $transport->sendMessage();
                            $this->messageManager->addSuccess(
                                __('Se ha enviado un correo electrónico.')
                            );
                            break;

                            case 2:
                            $transport = $this->_transportBuilder->setTemplateIdentifier('template_trabajaideal')
                            ->setTemplateOptions(['area' => 'frontend', 'store' => $store])
                            ->setTemplateVars(
                                [
                                    'store'     => $storeManager->getStore(),
                                    'time'    => $time,
                                    'place' => $place,
                                    'travel'     => $travel,
                                    'move'       => $move,
                                    'years'       => $years,
                                    
                                ]
                            )
                            ->setFrom('general')
                            ->addTo($emailSender, $nombre)
                            ->getTransport();
                            $transport->sendMessage();
                            $this->messageManager->addSuccess(
                                __('Se ha enviado un correo electrónico.')
                            );
                            break;

                            case 3:
                            $transport = $this->_transportBuilder->setTemplateIdentifier('template_trabaja')
                            ->setTemplateOptions(['area' => 'frontend', 'store' => $store])
                            ->setTemplateVars(
                                [
                                    'store'     => $storeManager->getStore(),
                                    'nombre'    => $nombre,
                                    'celular' => $celular,
                                    'email'     => $email,
                                    'area'      => $area,
                                    'nombre2'    => $nombre2,
                                    'celular2' => $celular2,
                                    'email2'     => $email2,
                                ]
                            )
                            ->setFrom('general')
                            ->addTo($emailSender, $nombre)
                            ->getTransport();
                            $transport->sendMessage();
                            $this->messageManager->addSuccess(
                                __('Se ha enviado un correo electrónico.')
                            );
                            break;
                        }
                        
                    }catch(\Exception $err){
                        error_log($err->getMessage());
                        $this->messageManager->addError(__($err->getMessage()));
                        $url = $backUrl;
                    }catch(\Magento\Framework\Exception\MailException $err){
                        error_log($err->getMessage());
                        $url = $backUrl;
                        $this->messageManager->addError(__("Formato de email incorrecto"));
                    }
                }else{
                    $url = $backUrl;
                    $this->messageManager->addError(__("El email de destino no es valido"));
                }
            }
        }else{
            $url = $storeManager->getStore()->getBaseUrl();
            $this->messageManager->addError(__("Esta url no se puede acceder directamente"));
        }
        $this->_redirect($url);
    }
}