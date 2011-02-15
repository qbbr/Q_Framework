<?php
/**
 * Управление новостями
 *
 * @author Sokolov Innokenty, <qbbr@qbbr.ru>
 * @copyright Copyright (c) 2010, qbbr
 */
class Q_News extends Q_AdminController
{
    /**
     * Index
     *
     * @access public
     * @param Q_Request $request
     * @return Q_Response 
     */
    public function indexAction(Q_Request $request)
    {
        $newsList = Doctrine::getTable('News')->findAll()->toArray();
        
        return new Q_Response(
            'index.html',
            array(
                'newsList' => $newsList
            )
        );
    }
    
    /**
     * Редактировать
     *
     * @access public
     * @param Q_Request $request
     * @return Q_Response 
     */
    public function editAction(Q_Request $request)
    {
        $newsList = Doctrine::getTable('News')->findAll()->toArray();
        $news = Doctrine::getTable('News')->find($request->id);

        if (!$news) Q_Router::error404();
        
        return new Q_Response(
            'index.html',
            array(
                'newsList' => $newsList,
                'news' => $news->toArray(),
                'saved' => Q_Flash::get('saved')
            )
        );
    }

    /**
     * Сохранить
     *
     * @access public
     * @param Q_Request $request
     * @return void
     */
    public function saveAction(Q_Request $request)
    {
        $news = new News();
        $news->assignIdentifier($request->id);
        $news->name = $this->post('name');
        $news->seo_title = $this->post('seo_title');
        $news->seo_keywords = $this->post('seo_keywords');
        $news->seo_description = $this->post('seo_description');
        $news->announce = $this->post('announce');
        $news->text = $this->post('text');
        $news->save();

        Q_Flash::set('saved', true);
        $this->redirect();
    }
}