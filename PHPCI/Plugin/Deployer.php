<?php
/**
 * PHPCI - Continuous Integration for PHP
 *
 * @copyright    Copyright 2015, Block 8 Limited.
 * @license      https://github.com/Block8/PHPCI/blob/master/LICENSE.md
 * @link         https://www.phptesting.org/
 */

namespace PHPCI\Plugin;

use b8\HttpClient;
use PHPCI\Builder;
use PHPCI\Model\Build;

/**
* Integrates PHPCI with Deployer: https://github.com/rebelinblue/deployer
* @author       Dan Cryer <dan@block8.co.uk>
* @package      PHPCI
* @subpackage   Plugins
*/
class Deployer extends AbstractPlugin
{
    /**
     * @inheritdoc
     */
    protected $allowedStages = array('complete', 'success', 'failure', 'fixed', 'broken');

    protected $webhookUrl;
    protected $reason;

    /**
     * Set up the plugin, configure options, etc.
     * @param Builder $phpci
     * @param Build $build
     * @param array $options
     */
    public function __construct(Builder $phpci, Build $build, array $options = array())
    {
        $this->phpci = $phpci;
        $this->build = $build;
        $this->reason = 'PHPCI Build #%BUILD% - %COMMIT_MESSAGE%';

        if (isset($options['webhook_url'])) {
            $this->webhookUrl = $options['webhook_url'];
        }

        if (isset($options['reason'])) {
            $this->reason = $options['reason'];
        }
    }

    /**
    * Copies files from the root of the build directory into the target folder
    */
    public function execute()
    {
        if (empty($this->webhookUrl)) {
            $this->phpci->logFailure('You must specify a webhook URL.');
            return false;
        }

        $http = new HttpClient();

        $response = $http->post($this->webhookUrl, array(
            'reason' => $this->phpci->interpolate($this->reason),
            'source' => 'PHPCI',
            'url' => $this->phpci->interpolate('%BUILD_URI%'),
        ));

        return $response['success'];
    }
}
