<?php
/**
 * Another poorly named controller
 * 
 * it's actually the default route
 *
 * words are hard
 */

class report extends Controller
{
    protected $id = null;

    public function index()
    {
        $this->tpl .= '/index';

        $stats = [];
        $cats = Cache::read('categories');
        foreach($cats as $id => $cat)
        {
            $stats[$id] = current(db()->query(
            'SELECT
                COUNT(*) as total_issues,
                COUNT(DISTINCT(email)) as unique_posters,
                GREATEST(MAX(edit_time),MAX(time),MAX(updated_at)) as last_activity
             FROM reports
             WHERE category = '.$id
            )->fetchAll(PDO::FETCH_ASSOC));

            if($stats[$id]['total_issues'])
            {
                if($cat['description'])
                    $field = 'description';
                elseif($cat['reproduce'])
                    $field = 'reproduce';
                elseif($cat['expected'])
                    $field = 'expected';
                elseif($cat['actual'])
                    $field = 'actual';
                else
                    $field = false;

                $field = $field ? 'r.' . $field . ' AS preview_text,' : '';

                $latest_issue = current(db()->query(
                    'SELECT r.id, r.subject, '.$field.' r.time, 
                        COUNT(c.id) AS comments
                        FROM reports AS r
                            LEFT JOIN comments AS c
                            ON r.id = c.report
                        WHERE r.category = '.$id.'
                        GROUP BY r.id
                        ORDER BY r.time DESC
                        LIMIT 1'
                )->fetchAll(PDO::FETCH_ASSOC));
                $latest_issue['tags'] = db()->query(
                    'SELECT tag 
                     FROM tag_joins 
                     WHERE report = '.$latest_issue['id']
                )->fetchAll(PDO::FETCH_NUM);
            } else {
                $latest_issue = null;
            }
            $stats[$id]['latest_issue'] = $latest_issue;

            $stats[$id]['open_issues'] = selectCount('reports','closed = 0 AND category = '.$id);
        }
        $this->data['stats'] = $stats;
    }

    // should implement this kind of abstraction in more places
    protected function checkId($id)
    {
        if($this->id!==null)
            return true;

        if(!selectCount('reports','id = '.(int)$id))
            $this->abort('No such report.');
        $this->id = (int)$id;
    }

    // individual reports
    public function view($id)
    {
        $this->checkId($id);

        $this->tpl .= '/view';

        $this->data += 
            current(
                db()->query(
                    'SELECT * FROM reports WHERE id = '.$this->id
                )->fetchAll(PDO::FETCH_ASSOC)
        );
        $this->data['comments'] = selectCount('comments','report = '.$this->id)             ? db()->query('SELECT * FROM comments WHERE report = '.$this->id
                )->fetchAll(PDO::FETCH_ASSOC) 
            : null;
        $this->data['category'] =  current(db()->query(
                'SELECT * FROM categories WHERE id = '.(int)$this->data['category']
            )->fetchAll(PDO::FETCH_ASSOC));
        $this->data['tags'] = db()->query(
            'SELECT tag
             FROM tag_joins 
             WHERE report = '.$this->id)->fetchAll(PDO::FETCH_NUM);

        exit;
    }

    // reports by category
    public function category($id)
    {
        if(!selectCount('categories','id = '.(int)$id))
            $this->abort('No such category.');

        $this->tpl .= '/category';

        $this->data = [
            'category' => current(db()->query(
                'SELECT * FROM categories WHERE id = '.(int)$id
            )->fetchAll(PDO::FETCH_ASSOC)),
            'reports' => db()->query(
                'SELECT id, subject, time, status, closed
                 FROM reports
                 WHERE category = '.(int)$id.'
                 ORDER BY closed ASC,
                    time DESC'
            )->fetchALL(PDO::FETCH_ASSOC)
        ];
    }

    // moderation actions
    public function action($id)
    {
        $this->requireLogin();
        $this->checkId($id);

        if(isset($_POST['status'],$_POST['updateStatus']))
            $this->updateStatus((int)$_POST['status']);

        if(isset($_POST['toggleClosed']))
            $this->toggleClosed();

        if(isset($_POST['delete']))
            $this->delete();

        $this->abort('What are you doing!? No GET access baka!');

    }

    protected function updateStatus($status)
    {
        if(selectCount('statuses','id = '.(int)$status))
        {
            db()->query(
                'UPDATE reports 
                 SET status = '.(int)$status
              .' WHERE id = '.$this->id
            );
            $this->flash[] = 'Status changed.';
        } else {
            $this->flash[] = 'No such status!';
        }
        $this->view($this->id);
    }

    protected function toggleClosed() 
    {
        db()->query(
            'UPDATE reports
             SET closed = NOT(closed)
             WHERE id = '.$this->id
        );
        $this->flash[] = 'k.';
        $this->view($this->id);
    }

    protected function delete()
    {
        $catid = db()->query(
            'SELECT category FROM reports WHERE id = '.$this->id
        )->fetchColumn(0);

        db()->query('DELETE FROM comments WHERE report = '.$this->id);
        db()->query('DELETE FROM reports WHERE id = '.$this->id);
        $this->flash[] = 'Report deleted.';
        $this->category($catid);
    }
}// this file could use some work
