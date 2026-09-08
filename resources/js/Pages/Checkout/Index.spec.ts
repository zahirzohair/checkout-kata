import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import Index from './Index.vue';

const { post } = vi.hoisted(() => ({ post: vi.fn() }));

vi.mock('@inertiajs/vue3', () => ({
    Head: { render: () => null },
    router: { post },
}));

beforeEach(() => {
    post.mockReset();
    globalThis.route = ((name: string) => name) as typeof globalThis.route;
});

const skus = [
    { sku: 'A', label: 'Item A' },
    { sku: 'B', label: 'Item B' },
];

describe('Checkout/Index', () => {
    it('renders a button for every sku with its label', () => {
        const wrapper = mount(Index, {
            props: { skus, scanned: [], totalCents: 0 },
        });

        const buttons = wrapper.findAll('button').filter((b) => b.text().includes('Item'));
        expect(buttons).toHaveLength(2);
        expect(buttons[0]!.text()).toContain('A');
        expect(buttons[0]!.text()).toContain('Item A');
    });

    it('shows "nothing scanned yet" when the basket is empty', () => {
        const wrapper = mount(Index, {
            props: { skus, scanned: [], totalCents: 0 },
        });

        expect(wrapper.text()).toContain('Nothing scanned yet.');
        expect(wrapper.text()).toContain('$0.00');
    });

    it('lists scanned lines and formats the total as dollars', () => {
        const wrapper = mount(Index, {
            props: {
                skus,
                scanned: [
                    { sku: 'A', quantity: 3 },
                    { sku: 'B', quantity: 2 },
                ],
                totalCents: 190,
            },
        });

        const text = wrapper.text();
        expect(text).toContain('x3');
        expect(text).toContain('x2');
        expect(text).toContain('$1.90');
    });

    it('posts the clicked sku to checkout.scan', async () => {
        const wrapper = mount(Index, {
            props: { skus, scanned: [], totalCents: 0 },
        });

        await wrapper.findAll('button')[0]!.trigger('click');

        expect(post).toHaveBeenCalledWith(
            'checkout.scan',
            { sku: 'A' },
            expect.objectContaining({
                preserveScroll: true,
                preserveState: true,
            }),
        );
    });

    it('disables the clicked button while the scan is in flight, then re-enables it', async () => {
        let finish!: () => void;
        post.mockImplementation((_url, _data, options) => {
            finish = options.onFinish;
        });

        const wrapper = mount(Index, {
            props: { skus, scanned: [], totalCents: 0 },
        });
        const buttons = wrapper.findAll('button');

        await buttons[0]!.trigger('click');
        expect(buttons[0]!.attributes('disabled')).toBeDefined();
        expect(buttons[1]!.attributes('disabled')).toBeUndefined();

        finish();
        await wrapper.vm.$nextTick();
        expect(buttons[0]!.attributes('disabled')).toBeUndefined();
    });

    it('shows the server error message when a scan is rejected', async () => {
        post.mockImplementation((_url, _data, options) => {
            options.onError({ sku: 'Unknown SKU: Z' });
            options.onFinish();
        });

        const wrapper = mount(Index, {
            props: { skus, scanned: [], totalCents: 0 },
        });

        await wrapper.findAll('button')[0]!.trigger('click');

        expect(wrapper.text()).toContain('Unknown SKU: Z');
    });

    it('disables the reset button when the basket is empty, and posts checkout.reset when clicked', async () => {
        const empty = mount(Index, {
            props: { skus, scanned: [], totalCents: 0 },
        });
        const resetButton = empty.findAll('button').find((b) => b.text() === 'Reset')!;
        expect(resetButton.attributes('disabled')).toBeDefined();

        const withItems = mount(Index, {
            props: { skus, scanned: [{ sku: 'A', quantity: 1 }], totalCents: 50 },
        });
        await withItems
            .findAll('button')
            .find((b) => b.text() === 'Reset')!
            .trigger('click');

        expect(post).toHaveBeenCalledWith(
            'checkout.reset',
            {},
            expect.objectContaining({ preserveScroll: true }),
        );
    });
});
