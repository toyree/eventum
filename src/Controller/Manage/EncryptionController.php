<?php

/*
 * This file is part of the Eventum (Issue Tracking System) package.
 *
 * @copyright (c) Eventum Team
 * @license GNU General Public License, version 2 or later (GPL-2+)
 *
 * For the full copyright and license information,
 * please see the COPYING and AUTHORS files
 * that were distributed with this source code.
 */

namespace Eventum\Controller\Manage;

use Eventum\Crypto\CryptoException;
use Eventum\Crypto\CryptoManager;
use Misc;
use Setup;
use User;

class EncryptionController extends ManageBaseController
{
    /** @var string */
    protected $tpl_name = 'manage/encryption.tpl.html';

    /** @var int */
    protected $min_role = User::ROLE_ADMINISTRATOR;

    /** @var string */
    private $cat;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $request = $this->getRequest();

        $this->cat = $request->request->get('cat') ?: $request->query->get('cat');
    }

    /**
     * @inheritdoc
     */
    protected function defaultAction()
    {
        switch ($this->cat) {
            case 'regenerate':
                $this->regenerateAction();
                break;
            case 'activate':
                $this->activateAction();
                break;
        }
    }

    private function regenerateAction()
    {
        try {
            CryptoManager::regenerateKey();
            Misc::setMessage(ev_gettext('Thank you, new key for encryption was generated.'));
        } catch (CryptoException $e) {
            Misc::setMessage(
                ev_gettext('Unable to generate new encryption key. Check server error logs.'), Misc::MSG_ERROR
            );
        }
    }

    private function activateAction()
    {
        $post = $this->getRequest()->request;
        $enable = $post->get('encryption');

        if (!$enable) {
            CryptoManager::disableEncryption();
            Misc::setMessage(ev_gettext('Encryption was disabled!'));

            return;
        }

        try {
            CryptoManager::enableEncryption();
            Misc::setMessage(ev_gettext('Encryption was enabled!'));
        } catch (CryptoException $e) {
            Misc::setMessage(
                ev_gettext('Encryption can not be enabled: %s', $e->getMessage()), Misc::MSG_ERROR
            );
        }
    }

    /**
     * @inheritdoc
     */
    protected function prepareTemplate()
    {
        $setup = Setup::get();
        $this->tpl->assign(
            array(
                'encryption' => $this->html->enableRadioButtons($setup['encryption']),
            )
        );
    }
}
