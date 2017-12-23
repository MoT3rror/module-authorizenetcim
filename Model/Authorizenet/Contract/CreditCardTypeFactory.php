<?php
/**
 * Pmclain_AuthorizenetCim extension
 * NOTICE OF LICENSE
 *
 * This source file is subject to the OSL 3.0 License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/osl-3.0.php
 *
 * @category  Pmclain
 * @package   Pmclain_AuthorizenetCim
 * @copyright Copyright (c) 2017-2018
 * @license   Open Software License (OSL 3.0)
 */

namespace Pmclain\AuthorizenetCim\Model\Authorizenet\Contract;

use net\authorize\api\contract\v1\CreditCardType;
use Pmclain\AuthorizenetCim\Model\Authorizenet\AbstractFactory;

class CreditCardTypeFactory extends AbstractFactory
{
  /**
   * @param $sourceData
   * @return CreditCardType
   */
  public function create($sourceData = null)
  {
    return $this->_objectManager->create(CreditCardType::class);
  }
}