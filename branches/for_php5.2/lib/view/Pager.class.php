<?php
/**
 * 分页类
 * @package view
 * @author xiezhenye
 */
class Pager {
	var $itemsCount = 10;
	var $placer;
	var $first = ' <a class="first" href="{url}">第一页</a>';
	var $last = ' <a class="last" href="{url}">最后一页</a>';
	var $prev = ' <a class="prev" href="{url}">上一页</a>';
	var $next = ' <a class="next" href="{url}">下一页</a>';
	
	var $pages = ' <span class="pages">{items}</span>';
	var $pageLink = ' <a class="page" href="{url}">{number}</a>';
	var $currentPage = ' <span class="current">{number}</span>';
	var $total = '<span class="total">共{number}页</span>';
	
	var $template = "<span class='pager'>{total}{first}{prev}{pages}{next}{last}</span>";
	
	function __construct($placer = '%d') {
		$this->placer = $placer;
	}
	
	/**
	 * 输出分页导航 HTML
	 *
	 * 
	 */
	function draw($current, $total, $pageSize, $urlPattern) {
		echo $this->getHtml($current, $total, $pageSize, $urlPattern);
	}
	
	/**
	 * 得到分页导航 HTML
	 * 
	 */
	function getHtml($current, $total, $pageSize, $urlPattern) {
		$totalPage = $total >= 0 ? intval(ceil($total / $pageSize)) : -1;
		$find = array('{total}', '{first}', '{prev}', '{pages}', '{next}', '{last}');
		$replace = array(
						$this->getTotal($totalPage),
						$this->getFirst($current, $urlPattern),
						$this->getPrev($current, $urlPattern),
						$this->getPages($current, $totalPage, $urlPattern),
						$this->getNext($current, $totalPage, $urlPattern),
						$this->getLast($current, $totalPage, $urlPattern)
					);
		$ret = str_replace($find, $replace, $this->template);
		return $ret;
	}
	
	function getPages($current, $totalPage, $urlPattern) {
		if ($totalPage < 0) {
			$pages = str_replace('{number}', $current, $this->currentPage);
			return str_replace('{items}', $pages, $this->pages);
		}
		$center = ceil($this->itemsCount / 2) - 1;
	    $begin = $current - $center;
		$end = $current + $this->itemsCount - $center - 1;
		if ($begin < 1) {
		    $begin = 1;
            $end = $this->itemsCount;
		}
		if ($end > $totalPage) {
		    $end = $totalPage;
            $begin = (($totalPage - $this->itemsCount) > 0)
            	? ($totalPage - $this->itemsCount + 1)
            	: 1;
		}
		$pages = '';
	    for ($i = $begin; $i <= $end; $i++) {
		    if ($i != $current) {
			    $pages .= str_replace(array('{number}', '{url}'), 
									  array($i, $this->replacePlacer($urlPattern, $i)),
									  $this->pageLink);
			} else {
			    $pages .= str_replace('{number}', $current, $this->currentPage);
			}
		}
		return str_replace('{items}', $pages, $this->pages);
	}
	
	private function replacePlacer($str, $n) {
		return str_replace($this->placer, $n, $str);
	}
	
	/**
	 * 第一页
	 * 
	 * @return string
	 */
	function getFirst($current, $urlPattern)
	{
	    if ($current > 1) {
	        return str_replace('{url}', $this->replacePlacer($urlPattern, 1), $this->first);
		}
		return '';
	}

	/**
	 * 最后一页
	 * 
	 * @return string
	 */
	function getLast($current, $total, $urlPattern)
	{
	    if ($current < $total) {
			return str_replace('{url}', $this->replacePlacer($urlPattern, $total), $this->last);
		}
		return '';
	}

	/**
	 * 上一页
	 * 
	 * @return string
	 */
	function getPrev($current, $urlPattern)
	{
		if ($current > 1) {
			return str_replace('{url}', $this->replacePlacer($urlPattern, $current - 1), $this->prev);
		}
		return '';
	}

	/**
	 * 下一页
	 * 
	 * @return string
	 */
	function getNext($current, $total, $urlPattern)
	{
		if ($current < $total || $total < 0) {
			return str_replace('{url}', $this->replacePlacer($urlPattern, $current + 1), $this->next);
		}
		return '';
	}
	
	/**
	 * 总页数
	 * 
	 * @return string
	 */
	function getTotal($total)
	{
		return str_replace('{number}', $total >=0 ? $total : '?', $this->total);
	}
}
