<?php
namespace WpSeek;

class Query
{
    public function getPostCount($post_type = 'post')
    {
        $posts = wp_count_posts($post_type);
        return $posts->publish;
    }

    public function postQuery($post_type = 'post', $num = 3, $sort = 'date')
    {
        $args = [
            'post_type' => $post_type,
            'posts_per_page' => $num,
            'post_status' => 'publish',
            'orderby' => $sort
        ];

        $query = new \WP_Query($args);

        return $query;
    }

    public function getPostTerms(int $post_id, string $taxonomy)
    {
        return get_the_terms($post_id, $taxonomy);
    }

    public function getRelatedTerms($terms)
    {
        $related = [];

        foreach ($terms as $term) {
            var_dump($term->name);
            $related[] = $term->term_id;
        }

        return $related;
    }

    private function getCategoryPosts(int $post_id, int $num = 3, string $post_type = 'current')
    {
        if ($post_type == 'current') {
            $post_type = get_post_type();
        }

        // get all categories attached to post
        $terms = $this->getPostTerms($post_id, 'category');

        if (!empty($terms)) {
            // get category term ids
            $related = $this->getRelatedTerms($terms);

            return new \WP_Query([
                'posts_per_page' => $num,
                'category__in' => $related,
                'post__not_in' => [$post_id],
                'post_type' => $post_type
            ]);
        }

        return;
    }

    private function getTagPosts(int $post_id, int $num = 3, string $post_type = 'current')
    {
        if ($post_type == 'current') {
            $post_type = get_post_type();
        }

        // get all categories attached to post
        $terms = $this->getPostTerms($post_id, 'post_tag');

        if (!empty($terms)) {
            // get category term ids
            $related = $this->getRelatedTerms($terms);

            return new \WP_Query([
                'posts_per_page' => $num,
                'tag__in' => $related,
                'post__not_in' => [$post_id],
                'post_type' => $post_type
            ]);
        }

        return;
    }

    private function getTaxPosts(int $post_id, string $taxonomy, int $num = 3, string $post_type = 'current')
    {
        if ($post_type == 'current') {
            $post_type = get_post_type();
        }

        // get all categories attached to post
        $terms = $this->getPostTerms($post_id, $taxonomy);

        if (!empty($terms)) {
            // get category term ids
            $related = $this->getRelatedTerms($terms);

            // build category post query
            return new \WP_Query([
                'posts_per_page' => $num,
                'post_type' => $post_type,
                'post__not_in' => [$post_id],
                'tax_query' => [
                    [
                        'taxonomy' => $taxonomy,
                        'terms'    => $related,
                        'field'    => 'term_id',
                    ],
                ],
            ]);
        }

        return;
    }

    public function getRelatedPosts(
        $taxonomy = 'category',
        $num = 3,
        $post_type = 'current'
    ) {
        $post_id = get_the_ID();

        // config
        if ($taxonomy === 'category') {
            return $this->getCategoryPosts($post_id, $num, $post_type);
        } elseif ($taxonomy === 'post_tag') {
            return $this->getTagPosts($post_id, $num, $post_type);
        }

        return $this->getTaxPosts($post_id, $taxonomy, $num, $post_type);
    }

    public function getQueryPostCount($query)
    {
        return $query->post_count;
    }
}
