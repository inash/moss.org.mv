<?php

/**
 * Users model class.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Wed Aug 19, 2009 12:37 AM
 */

class Default_Model_Users extends Pub_Model
{
	protected $userId;
	protected $name;
	protected $email;
	protected $password;
	protected $memberType = 'Individual';
	protected $primaryGroup = 'member';
	protected $website;
	protected $company;
	protected $location;
	protected $dateRegistered;
	protected $dateLastLogin;
	protected $active = 'N';
	protected $reset = 'N';
	protected $disabled = 'N';
	
	public function getUserId() {
		return $this->userId;
	}

	public function setUserId($userId) {
		$this->userId = $userId;
	}

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function getEmail() {
		return $this->email;
	}

	public function setEmail($email) {
		$this->email = $email;
	}

	public function getPassword() {
		return $this->password;
	}

	public function setPassword($password) {
		$this->password = $password;
	}

	public function getMemberType() {
		return $this->memberType;
	}

	public function setMemberType($memberType) {
		$this->memberType = $memberType;
	}

	public function getPrimaryGroup() {
		return $this->primaryGroup;
	}

	public function setPrimaryGroup($primaryGroup) {
		$this->primaryGroup = $primaryGroup;
	}

	public function getWebsite() {
		return $this->website;
	}

	public function setWebsite($website) {
		$this->website = $website;
	}

	public function getCompany() {
		return $this->company;
	}

	public function setCompany($company) {
		$this->company = $company;
	}

	public function getLocation() {
		return $this->location;
	}

	public function setLocation($location) {
		$this->location = $location;
	}

	public function getDateRegistered() {
		return $this->dateRegistered;
	}

	public function setDateRegistered($dateRegistered) {
		$this->dateRegistered = $dateRegistered;
	}

	public function getDateLastLogin() {
		return $this->dateLastLogin;
	}

	public function setDateLastLogin($dateLastLogin) {
		$this->dateLastLogin = $dateLastLogin;
	}

	public function getActive() {
		return $this->active;
	}

	public function setActive($active) {
		$this->active = $active;
	}

	public function getReset() {
		return $this->reset;
	}

	public function setReset($reset) {
		$this->reset = $reset;
	}

	public function getDisabled() {
		return $this->disabled;
	}

	public function setDisabled($disabled) {
		$this->disabled = $disabled;
	}
	
	public function isFeePending()
	{
		return $this->getMapper()->isFeePending($this->getUserId());
	}
}
