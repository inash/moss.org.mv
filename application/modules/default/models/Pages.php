<?php

/**
 * Pages model. Abstracts the application/data source mapping for Pages.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Sun Aug 23, 2009 08:04 PM
 */

class Default_Model_Pages extends Pub_Model
{
	protected $pageId;
	protected $name;
	protected $title;
	protected $dateCreated;
	protected $dateModified;
	protected $pageRevisionId;
	protected $createdBy;
	protected $modifiedBy;
	protected $body;
	protected $table;
	protected $published = 'Published';
	
	public function getPageId() {
		return $this->pageId;
	}

	public function setPageId($pageId) {
		$this->pageId = $pageId;
	}

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function getTitle() {
		return $this->title;
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function getDateCreated() {
		return $this->dateCreated;
	}

	public function setDateCreated($dateCreated) {
		$this->dateCreated = $dateCreated;
	}

	public function getDateModified() {
		return $this->dateModified;
	}

	public function setDateModified($dateModified) {
		$this->dateModified = $dateModified;
	}
	
    public function getPageRevisionId() {
        return $this->pageRevisionId;
    }

    public function setPageRevisionId($pageRevisionId) {
        $this->pageRevisionId = $pageRevisionId;
    }

	public function getCreatedBy() {
		return $this->createdBy;
	}

	public function setCreatedBy($createdBy) {
		$this->createdBy = $createdBy;
	}

	public function getModifiedBy() {
		return $this->modifiedBy;
	}

	public function setModifiedBy($modifiedBy) {
		$this->modifiedBy = $modifiedBy;
	}

	public function getBody() {
		return $this->body;
	}

	public function setBody($body) {
		$this->body = $body;
	}

	public function getTable() {
		return $this->table;
	}

	public function setTable($table) {
		$this->table = $table;
	}

	public function getPublished() {
		return $this->published;
	}

	public function setPublished($published) {
		$this->published = $published;
	}
	
	public function fetchFullTextMatches($string)
	{
		$entries = $this->getMapper()->fetchFullTextMatches($string);
		if (count($entries) == 0) return false;
		return $entries;
	}
}
