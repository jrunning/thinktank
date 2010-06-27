<?php
/**
 * Embed.ly Plugin configuration controller
 * @author Jordan Running <jordan[at]jordanrunning[dot]com>
 *
 */
class EmbedlyConfigurationController extends ThinkTankAuthController {
    /**
     *
     * @var Owner
     */
    var $owner;
    /**
     * Constructor
     * @param Owner $owner
     */
    public function __construct($owner) {
        parent::__construct(true);
        $this->owner = $owner;
    }

    public function authControl() {
        $config = Config::getInstance();
        $this->setViewTemplate($config->getValue('source_root_path').'webapp/plugins/embedly/view/embedly.account.index.tpl');
        $this->addToView('message', 'Hello, world! This is the example plugin configuration page for  '.$this->owner->user_email .'.');
        return $this->generateView();
    }
}
