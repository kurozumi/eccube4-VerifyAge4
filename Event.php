<?php

namespace Plugin\VerifyAge4;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Eccube\Request\Context;
use Eccube\Event\TemplateEvent;

/**
 * @author Akira Kurozumi <info@a-zumi.net>
 */
class Event implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => [['onKernelController', 100000000]]
        ];
    }

    /**
     * @var \Eccube\Request\Context
     */
    protected $requestContext;
    
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function __construct(Context $requestContext, EventDispatcherInterface $eventDispatcher)
    {
        $this->requestContext = $requestContext;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        // フロントページではない場合スルー
        if(!$this->requestContext->isFront()) {
            return;
        }
        
        if ($event->getRequest()->attributes->has('_template')) {
            $template = $event->getRequest()->attributes->get('_template');
            $this->eventDispatcher->addListener($template->getTemplate(), function (TemplateEvent $templateEvent) {
                $templateEvent->addAsset('@VerifyAge4/default/asset.twig');
                $templateEvent->addSnippet('@VerifyAge4/default/snippet.twig');
                
                // snippet.twigに値を渡す
                // 文言を動的に操作したい場合はこのへんで調整してください
                $templateEvent->setParameter('modalTitle', "年齢確認");
                $templateEvent->setParameter('modalDescription', "20歳以上ですか？");
            });
        }
    }
}
