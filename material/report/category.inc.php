<?php
//
// reports by category : this isn't a message board, honest!
//
$cat = $this->data['categories'][$this->data['category_id']];
$pageTitle = $cat['title'];

require BUNZ_TPL_DIR . 'header.inc.php';
?>
<script src="<?= BUNZ_JS_DIR,'highlight.js' ?>"></script>
<script>hljs.initHighlightingOnLoad();</script>
<div style="height: 100%;">
<!--
    about:category
-->
<div class="row">
    <div class="col s12">
        <article>
            <div class="row">
                <!-- 
                    title 
                -->
                <section class='section col s8 z-depth-5 category-<?= $cat['id'] ?>-text'>
                    <h4 class="category-<?= $cat['id'] ?>-text <?= $cat['icon'] ?>"><?= $cat['title'] ?></h4>
                    <h6><?= $cat['caption'] ?></h6>
                </section>
                <!--
                    actions
                -->
                <section class="col s4 right-align">
                    
<?php
if($this->auth())
{
?>
                    <a href="<?=BUNZ_HTTP_DIR,'admin/edit/category/',$cat['id']?>" 
                       class="btn btn-floating z-depth-5 transparent" 
                       title="submit new"><i class="green-text darken-2 icon-pencil-alt"></i></a>
<?php
}
?>
                    <a href="<?=BUNZ_HTTP_DIR,'post/category/',$cat['id']?>" 
                       class="btn btn-floating z-depth-5 transparent" 
                       title="submit new"><i class="green-text darken-2 icon-plus"></i></a>
                </section>
        </article>
    </div>
</div>
<?php
if(empty($this->data['reports']))
{
?>
        <!--
            consistency++
        -->
        <div class="z-depth-5 yellow section flow-text icon-attention center-align blue-text">Nothing here yet! <a class="btn-flat icon-plus" href="<?= BUNZ_HTTP_DIR,'post/category/',$cat['id'] ?>">Submit Something!</a></div>
<?php
} else {

?>
        <!--
            kill me now
            ok I will
        -->
<script src="/bunzilla/material/list.min.js"></script>
<script>
//
// list.js! http://listjs.com
//
document.body.onload = function(){
    var options = {
            valueNames: [
'closed','subject','comments','submitted','lastactive','status','priority'
        ]
    },
    myList = new List('list', options);
};
</script>

<!--
    dear god
-->
<div class="category-<?= $cat['id'] ?>-base z-depth-2" id="list">
    <div class="section no-pad-bot ">
    <div class="row black white-text z-depth-1"  id="fuck"><!-- me -->
    <div class="col s12 m4 ">

        <div class="col s3  right-align">
        <button data-sort="closed" 
           class="sort btn-flat waves-effect waves-light icon-lock tooltipped" data-position="bottom" data-tooltip="sort by open/closed"
        ><i class="icon-sort"></i></button>
        </div>

        <div class="col s3  center-align">
        <button data-sort="priority" 
           class="sort btn-flat waves-effect waves-light icon-attention tooltipped" data-position="bottom" data-tooltip="sort by priority"
        ><i class="icon-sort"></i></button>
        </div>

        <div class="col s3  left-align">
        <button data-sort="status" 
           class="sort btn-flat waves-effect waves-light icon-pinboard tooltipped" data-position="bottom" data-tooltip="sort by status"
        ><i class="icon-sort"></i></button>
        </div>

    </div>
    <div class="col s12 m8 ">
        <div class="col s3  right-align">
        <button data-sort="subject" 
           class="sort btn-flat waves-effect waves-light icon-doc-text-inv tooltipped" data-position="bottom" data-tooltip="sort by subject"
        ><i class="icon-sort"></i></button>
        </div>
        <div class="col s3  center-align">
        <button data-sort="comments" 
           class="sort btn-flat waves-effect waves-light icon-chat tooltipped" data-position="bottom" data-tooltip="sort by # comments"
        ><i class="icon-sort"></i></button>
        </div>
        <div class="col s3  center-align">
        <button data-sort="submitted" 
           class="sort btn-flat waves-effect waves-light icon-time tooltipped" data-position="bottom" data-tooltip="sort by submission time"
        ><i class="icon-sort"></i></button>

        </div>
        <div class="col s3  left-align">
        <button data-sort="lastactive" 
           class="sort btn-flat waves-effect waves-light icon-time tooltipped" data-position="bottom" data-tooltip="sort by last activity"
        ><i class="icon-sort"></i></button>
        </div>
    </div>
    </div>
    </div><!-- asdfasdfasdfasdf -->

    <ul class="list collapsible category-<?= $cat['id'] ?>-base">
<?php
//
// tidy can be used to fix up html from truncated message "previews"
// but it's not required because we can just strip_tags()
//
    $tidy = extension_loaded('tidy') ? new tidy() : false;

    foreach($this->data['reports'] as $i => $report)
    {
        $report['last_active'] = max($report['time'],$report['updated_at'],$report['edit_time']);

/**
 * logical smooth sailing 
 * shoutouts to sorttable.js tho 
 *
 * check the history for this file if the above comment doesn't make any sense
 */
?>
        <li>
<?php // these values are hidden by/for purely presentational purposes ?>
            <div class="gone">
            <span class="closed"><?= $report['closed'] ?></span>
            <span class="priority"><?= $report['priority'] ?></span>
            <span class="status"><?= $this->data['statuses'][$report['status']]['title'] ?></span>
            <span class="submitted"><?= date('YmdHis', $report['time']) ?></span>
            <span class="lastactive"><?= date('YmdHis', $report['last_active']) ?></span>
            <span class="comments"><?= $report['comments'] ?></span>
            </div>

<?php // it looks like a lot of markup because it is. ?>
            <div class="collapsible-header no-select">
                <div class="row">

<?php // [icon] subject line blablabla [status] ?>
                    <div class="col s12 z-depth-5">

                        <span class="left">
 <?= $report['closed'] ? '<i class="icon-lock grey-text" title="CLOSED."></i>' : priority($report['priority'],1) 
?>
                        </span>

                        <span class="subject-line h4">
                            <a class="flow-text" href="<?= BUNZ_HTTP_DIR, 'report/view/',$report['id'],'?material'?>"><?= $report['subject'] ?></a>
                        </span>

                        <span class="right"><?= status($report['status']) ?></span>

                    </div>
<?php // x comments | 4 hours ago | [php] [DIVitis] ?>
                    <div class="col left">

                        <span class="badge right blue-text" title="comments">
                            <a class=" icon-chat" href="<?= BUNZ_HTTP_DIR, 'report/view/',$report['id'],'?material#comments'?>"><?= $report['comments'] ?></a>
                        </span>

                    </div>

                    <div class="col s8">
                        <span class="submitted icon-history small" title="submitted at"><?= datef($report['time']) ?></span>

<?php // no point in redundancy ?>
<?= 
($report['last_active'] == $report['time']) ? '' 
: '<p class="icon-time small" title="last active">'.datef($report['last_active']).'</p>' 
?>
                    </div>
<?php 
//
// tags!
//
        if(!empty($report['tags']))
        {
            echo '<div class="right right-align icon-tags">';
            foreach($report['tags'] as $tag)
                echo tag($tag[0],0);
            echo '</div>';
        }
?>
                </div>
            </div>
            <div class="collapsible-body">
                <blockquote class=" category-<?= $cat['id'] ?>-text z-depth-4 icon-article-alt"><span class="subject"><?= $report['subject'] ?></span><?=
$report['edit_time'] ? '<p class="icon-pencil-alt"><a class="icon-time" href="'.BUNZ_DIFF_DIR.'reports/'.$report['id'].'">'.datef($report['edit_time']).'</a></p>' : '' 
?><div class="divider"></div>
<?php
//
// as mentioned above, cleaning up message previews in case they're too long
// and/or contain HTML
//
        if(isset($report['preview_text']))
        {
            if(strlen(strip_tags($report['preview_text'])) > 400)
            {
                if($tidy)
                {            
                    $report['preview_text'] = substr($report['preview_text'],0,400);
                    $report['preview_text'] = $tidy->repairString($report['preview_text'],["doctype" => "omit","show-body-only" => "yes"]);
                    $report['preview_text'] = preg_replace('/.*\<body\>(.*)\<\/body\>.*\<\/html\>/mis', '$1', $report['preview_text']);
                } else {
                    $report['preview_text'] = substr(strip_tags($report['preview_text']),0,100);
                }
                $report['preview_text'] .= '. . .';
            }
            echo $report['preview_text'];
        }  
?>
                    <p class="section no-pad-bot"><a class="icon-doc-text-inv btn-flat category-<?= $cat['id'] ?>-darken-2 waves-effect" 
                          href="<?= BUNZ_HTTP_DIR,'report/view/',$report['id'],'?material'?>">Full Report &rarr;</a></p>
                </blockquote>
            </div>
        </li>       
<?php
    }
?>
    </ul>
</div>
<?php
}
?>
</div>
<?php
require BUNZ_TPL_DIR .'footer.inc.php';
