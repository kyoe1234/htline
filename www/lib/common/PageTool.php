<?php
/**
 * @brief 페이징에 도움되는 static 메소드를 제공
 *
 * @author A2
 * @date 2011-03-08
 */
class PageTool {
    /**
     * @brief 페이징을 하기 위해 필요한 정보를 배열로 반환한다.\n
     * 시작과 끝 번호, 시작과 끝 페이지등 페이징을 위해 필요한 수치를 계산하여 배열로 반환한다.
     *
     * @param int 전체 아이템 개수
     * @param int 현재 페이지
     * @param int=10 페이지당 아이템 개수
     * @param int=10 블럭당 페이지 개수
     * @return array
     */
    public static function cacl($totalItem, $currentPage, $itemPerPage = 10, $pagePerBlock = 10) {
        ### 계산에 문제없도록 ###
        settype($currentPage, 'int'); // 보통 사용자에 의해 전달되는 값이므로


        ### 잘못된 값 수정 ###
        if ( $totalItem < 0 ) $totalItem = 0; // 최소한 전체 0개
        if ( $currentPage < 1 ) $currentPage = 1; // 최소한 1페이지
        if ( $itemPerPage < 1 ) $itemPerPage = 1; // 최소한 1개
        if ( $pagePerBlock < 1 ) $pagePerBlock = 1; // 최소한 1개 페이지


        ### 페이지 계산 ###
        $totalPage = ceil($totalItem / $itemPerPage);
        if ( $totalPage < 1 ) $totalPage = 1; // 최소한 1페이지
        // 현재 페이지 번호는 전체 페이지 수를 넘을 수 없다.
        if ( $currentPage > $totalPage ) $currentPage = $totalPage;
        $startNum = ($currentPage - 1) * $itemPerPage + 1;
        // 시작 번호가 전체 아이템 수를 넘지 않도록
        if ( $startNum > $totalItem ) $startNum = $totalItem;
        $endNum = $startNum + $itemPerPage - 1;
        // 끝 번호가 전체 아이템 수를 넘지 않도록
        if ( $endNum > $totalItem ) $endNum = $totalItem;


        ### 블럭 계산 ###
        $totalBlock = ceil($totalPage / $pagePerBlock);
        $currentBlock = ceil($currentPage / $pagePerBlock);
        $startPage = ($currentBlock - 1) * $pagePerBlock + 1;
        $endPage = $startPage + $pagePerBlock - 1;
        // 페이지 끝 번호가 전체 페이지 수를 넘지 않도록
        if ( $endPage > $totalPage ) $endPage = $totalPage;


        ### 이전, 다음 페이지 계산 ###
        $prevPage = ($currentPage > 1) ? $currentPage - 1 : 0;
        $nextPage = ($currentPage < $totalPage) ? $currentPage + 1 : 0;
        ### 위, 아래 블럭 시작 페이지 계산 ###
        $prevBlockPage = ($startPage > 1) ? $startPage - 1 : 0;
        $nextBlockPage = ($endPage < $totalPage) ? $endPage + 1 : 0;


        ### 목록의 아이템 시작 번호 ###
        $itemNo = ($totalPage - $currentPage + 1) * $itemPerPage - (($totalPage * $itemPerPage) - $totalItem);


        ### 페이지 정보를 담아 반환할 배열선언 ###
        return array(
            'totalItem' => $totalItem, // 전체 아이템 개수
            'totalPage' => $totalPage, // 전체 페이지
            'currentPage' => $currentPage, // 현재 페이지
            'itemPerPage' => $itemPerPage, // 페이지당 아이템 개수
            'pagePerBlock' => $pagePerBlock, // 블럭당 페이지 개수
            'startNum' => $startNum, // 현재 페이지의 시작 번호
            'endNum' => $endNum, // 현재 페이지의 끝 번호
            'totalBlock' => $totalBlock, // 전체 블럭 개수
            'currentBlock' => $currentBlock, // 현재 블럭 번호
            'startPage' => $startPage, // 현재 블럭의 시작 페이지
            'endPage' => $endPage, // 현재 블럭의 끝 페이지
            'prevPage' => $prevPage, // 이전 페이지
            'nextPage' => $nextPage, // 다음 페이지
            'prevBlockPage' => $prevBlockPage, // 이전 블럭의 시작 페이지
            'nextBlockPage' => $nextBlockPage, // 다음 블럭의 시작 페이지
            'itemNo' => $itemNo, // 목록의 아이템 시작 번호
        );
    }

    private function __construct() {}
}
?>