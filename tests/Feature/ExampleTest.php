<?php

it('redirects the home page to the orders list', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('orders.index'));
});
