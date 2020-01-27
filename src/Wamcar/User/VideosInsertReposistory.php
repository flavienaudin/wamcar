<?php


namespace Wamcar\User;



interface VideosInsertReposistory
{

    /**
     * @param VideosInsert $videosInsert
     */
    public function add(VideosInsert $videosInsert): VideosInsert;

    /**
     * @param VideosInsert $videosInsert
     */
    public function update(VideosInsert $videosInsert): VideosInsert;

    /**
     * @param VideosInsert $videosInsert
     */
    public function remove(VideosInsert $videosInsert): void;
}