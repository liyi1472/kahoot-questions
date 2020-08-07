<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HSK 出题系统 for Kahoot! - HSK-KAHOOT.INOCHI.ICU</title>
    <!-- Semantic UI -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css" integrity="sha512-8bHTC73gkZ7rZ7vpqUQThUDhqcNFyYi2xgDgPDHc+GXVGHXq+xPjynxIopALmOPqzo9JZj0k6OqqewdGO3EsrQ==" crossorigin="anonymous" />
    <!-- 自定义 -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- 滚动屏幕菜单 -->
    <div class="ui large top fixed hidden menu">
        <div class="ui container">
            <a href="" class="active item">主页</a>
        </div>
    </div>

    <!-- 手机屏幕菜单 -->
    <div class="ui vertical inverted sidebar menu">
        <a href="" class="active item">主页</a>
    </div>

    <!-- 主页内容 -->
    <div class="pusher">

        <!-- 上半 -->
        <div class="ui inverted vertical masthead center aligned segment">
            <!-- PC屏幕菜单 -->
            <div class="ui container">
                <div class="ui large secondary inverted pointing menu">
                    <a class="toc item">
                        <i class="sidebar icon"></i>
                    </a>
                    <a href="" class="active item">主页</a>
                </div>
            </div>
            <!-- 系统名称 -->
            <div class="ui text container">
                <h1 class="ui inverted header">HSK 出题系统</h1>
                <h2>for Kahoot!</h2>
            </div>
        </div>

        <!-- 下半 -->
        <div class="ui vertical segment">
            <div class="ui container">
                <form class="ui form" action="generator.php" method="post" enctype="multipart/form-data">
                    <div class="field">
                        <label>考试范围 (No.)</label>
                        <div class="two fields">
                            <div class="field">
                                <input type="number" name="from" placeholder="从" required value="1">
                            </div>
                            <div class="field">
                                <input type="number" name="to" placeholder="至" required value="100">
                            </div>
                        </div>
                    </div>
                    <div class="field">
                        <label>出题数量</label>
                        <div class="ui selection dropdown">
                            <input type="hidden" name="quantity" value="10">
                            <i class="dropdown icon"></i>
                            <div class="default text">共</div>
                            <div class="menu">
                                <div class="item" data-value="10">10道</div>
                                <div class="item" data-value="20">20道</div>
                                <div class="item" data-value="30">30道</div>
                                <div class="item" data-value="50">50道</div>
                                <div class="item" data-value="100">100道</div>
                            </div>
                        </div>
                    </div>
                    <div class="field">
                        <label>最新单词表</label>
                        <div class="field">
                            <input type="file" name="glossary" required>
                        </div>
                    </div>
                    <button class="ui secondary button" type="submit">提交</button>
                </form>
            </div>
        </div>

    </div>

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" integrity="sha512-bLT0Qm9VnAYZDflyKcBaQ2gg0hSYNQrJ8RilYldYQ1FxQYoCLtUjuuRuZo+fjqhx/qtq/1itJ0C2ejDxltZVFg==" crossorigin="anonymous"></script>
    <!-- Semantic UI -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js" integrity="sha512-dqw6X88iGgZlTsONxZK9ePmJEFrmHwpuMrsUChjAw1mRUhUITE5QU9pkcSox+ynfLhL15Sv2al5A0LVyDCmtUw==" crossorigin="anonymous"></script>
    <!-- 自定义 -->
    <script src="js/master.js"></script>
</body>

</html>