<?php

/**
 * Abstract Application Controller.
 * 
 * This is the abstract application controller which extends the abstract
 * default controller and enables authenticated and application level
 * functionality for the users/members.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Mon May 25, 2009 06:11 AM
 * @version $Id$
 */

abstract class ApplicationController extends DefaultController
{
	public function preDispatch()
	{
		parent::preDispatch();
	}
}
