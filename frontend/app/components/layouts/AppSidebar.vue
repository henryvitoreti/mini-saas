<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { getLogo, getLogoIcon } from '@/config/appLogos';
import { sidebarItems, type SidebarItem } from '@/config/sidebarItems';
import type { AppSidebarProps } from '@/types/ui/layout';

const props = defineProps<AppSidebarProps>();

const emit = defineEmits<{
  closeSidebar: [];
}>();

const menus = reactive<SidebarItem[]>(sidebarItems);
const route = useRoute();
const auth = useAuth();

const sidebarCollapsed = useState<boolean>('sidebar-collapsed', () => false);
const sidebarUsesCollapsedLayout = computed<boolean>(() => sidebarCollapsed.value && !props.sidebarOpen);

const activeFlyoutMenu = ref<string|null>(null);
const flyoutTop = ref<number>(96);
const closeFlyoutTimeout = ref<ReturnType<typeof setTimeout>|null>(null);

function canShowItem(item: SidebarItem): boolean {
  let response = item.allowed || item.show;

  if (item.onlyAdmin) {
    response = useRuntimeConfig().public.baseUrl === useRequestURL().origin
  }

  return response;
}

function isItemDisabled(item: SidebarItem): boolean {
  return !item.allowed && item.show;
}

function getVisibleChildren(item: SidebarItem): SidebarItem[] {
  return item.children?.filter((child: SidebarItem): boolean => canShowItem(child)) ?? [];
}

function itemHasChildren(item: SidebarItem): boolean {
  return getVisibleChildren(item).length > 0;
}

const visibleSidebarItems = computed<SidebarItem[]>(() => {
  return menus.filter((item: SidebarItem): boolean => canShowItem(item));
});

const activeFlyoutItem = computed<SidebarItem|null>(() => {
  if (activeFlyoutMenu.value === null) {
    return null;
  }

  return visibleSidebarItems.value.find((item: SidebarItem): boolean => item.key === activeFlyoutMenu.value) ?? null;
});

function isItemRouteActive(item: SidebarItem): boolean {
  if (!item.to) {
    return false;
  }

  if (item.to === '/') {
    return route.path === '/';
  }

  return route.path === item.to || route.path.startsWith(`${item.to}/`);
}

function isItemActive(item: SidebarItem): boolean {
  if (isItemRouteActive(item)) {
    return true;
  }

  return getVisibleChildren(item).some((child: SidebarItem): boolean => isItemActive(child));
}

function isMenuOpen(item: SidebarItem): boolean {
  return item.opened ?? isItemActive(item);
}

function toggleSidebarCollapsed(): void {
  sidebarCollapsed.value = !sidebarCollapsed.value;

  localStorage.setItem('sidebar-collapsed', sidebarCollapsed.value ? 'true' : 'false');

  if (!sidebarUsesCollapsedLayout.value) {
    activeFlyoutMenu.value = null;
  }
}

function toggleMenu(item: SidebarItem): void {
  item.opened = !isMenuOpen(item);
}

function clearCloseFlyoutTimeout(): void {
  if (closeFlyoutTimeout.value !== null) {
    clearTimeout(closeFlyoutTimeout.value);
    closeFlyoutTimeout.value = null;
  }
}

function openFlyout(item: SidebarItem, event: MouseEvent|null = null): void {
  if (!sidebarUsesCollapsedLayout.value || !itemHasChildren(item) || isItemDisabled(item)) {
    return;
  }

  clearCloseFlyoutTimeout();

  if (event?.currentTarget instanceof HTMLElement) {
    const rect = event.currentTarget.getBoundingClientRect();
    flyoutTop.value = Math.max(88, rect.top);
  }

  activeFlyoutMenu.value = item.key;
}

function scheduleCloseFlyout(): void {
  clearCloseFlyoutTimeout();

  closeFlyoutTimeout.value = setTimeout((): void => {
    activeFlyoutMenu.value = null;
  }, 1000);
}

function closeFlyout(): void {
  clearCloseFlyoutTimeout();
  activeFlyoutMenu.value = null;
}

function handleMenuClick(item: SidebarItem, event: MouseEvent|null = null): void {
  if (isItemDisabled(item) || !itemHasChildren(item)) {
    return;
  }

  if (sidebarUsesCollapsedLayout.value) {
    openFlyout(item, event);

    return;
  }

  toggleMenu(item);
}

function closeMobileSidebar(): void {
  closeFlyout();
  emit('closeSidebar');
}

async function logout(): Promise<void> {
  closeFlyout();
  await auth.logout();
  emit('closeSidebar');
}
</script>

<template>
  <aside class="app-sidebar" :class="{ 'is-open': props.sidebarOpen }">
    <div class="app-sidebar-brand">
      <NuxtLink to="/" class="app-sidebar-logo-link">
        <img
            v-if="!sidebarUsesCollapsedLayout"
            class="app-sidebar-logo-full"
            :src="getLogo()"
            alt="Minski"
        >

        <img
            v-else
            class="app-sidebar-logo-icon"
            :src="getLogoIcon()"
            alt="Minski"
        >
      </NuxtLink>
    </div>

    <nav class="app-sidebar-nav">
      <template v-for="item in visibleSidebarItems" :key="item.key">
        <button
            v-if="!itemHasChildren(item) && isItemDisabled(item)"
            class="app-sidebar-link is-disabled"
            type="button"
            disabled
        >
          <span class="app-sidebar-link-icon">
            <i v-if="item.icon" :class="item.icon"></i>
          </span>

          <span class="app-sidebar-link-label">{{ item.label }}</span>

          <span class="app-sidebar-lock">
            <i class="fa-solid fa-lock"></i>
          </span>
        </button>

        <NuxtLink
            v-else-if="!itemHasChildren(item)"
            :to="item.to"
            class="app-sidebar-link"
            :class="{ 'is-active': isItemActive(item) }"
            @click="closeMobileSidebar"
        >
          <span class="app-sidebar-link-icon">
            <i v-if="item.icon" :class="item.icon"></i>
          </span>

          <span class="app-sidebar-link-label">{{ item.label }}</span>
        </NuxtLink>

        <template v-else>
          <button
              class="app-sidebar-link"
              :class="{ 'is-active': isItemActive(item), 'is-disabled': isItemDisabled(item) }"
              type="button"
              :disabled="isItemDisabled(item)"
              @click="handleMenuClick(item, $event)"
              @mouseenter="openFlyout(item, $event)"
              @mouseleave="scheduleCloseFlyout"
          >
            <span class="app-sidebar-link-icon">
              <i v-if="item.icon" :class="item.icon"></i>
            </span>

            <span class="app-sidebar-link-label">{{ item.label }}</span>

            <span v-if="isItemDisabled(item)" class="app-sidebar-lock">
              <i class="fa-solid fa-lock"></i>
            </span>

            <span
                v-else
                class="app-sidebar-link-arrow"
                :class="{ 'rotate-180': isMenuOpen(item) }"
            >
              <i class="fa-solid fa-chevron-down"></i>
            </span>
          </button>

          <div
              v-show="isMenuOpen(item) && !sidebarUsesCollapsedLayout"
              class="app-sidebar-submenu"
          >
            <template v-for="child in getVisibleChildren(item)" :key="child.key">
              <button
                  v-if="!itemHasChildren(child) && isItemDisabled(child)"
                  class="app-sidebar-sublink is-disabled"
                  type="button"
                  disabled
              >
                <i v-if="child.icon" :class="child.icon"></i>
                <span>{{ child.label }}</span>

                <span class="app-sidebar-lock">
                  <i class="fa-solid fa-lock"></i>
                </span>
              </button>

              <NuxtLink
                  v-else-if="!itemHasChildren(child)"
                  :to="child.to"
                  class="app-sidebar-sublink"
                  :class="{ 'is-active': isItemActive(child) }"
                  @click="closeMobileSidebar"
              >
                <i v-if="child.icon" :class="child.icon"></i>
                <span>{{ child.label }}</span>
              </NuxtLink>

              <template v-else>
                <button
                    class="app-sidebar-sublink app-sidebar-sublink-button"
                    :class="{ 'is-active': isItemActive(child), 'is-disabled': isItemDisabled(child) }"
                    type="button"
                    :disabled="isItemDisabled(child)"
                    @click="toggleMenu(child)"
                >
                  <i v-if="child.icon" :class="child.icon"></i>
                  <span>{{ child.label }}</span>

                  <span v-if="isItemDisabled(child)" class="app-sidebar-lock">
                    <i class="fa-solid fa-lock"></i>
                  </span>

                  <i
                      v-else
                      class="fa-solid fa-chevron-down app-sidebar-nested-arrow"
                      :class="{ 'rotate-180': isMenuOpen(child) }"
                  ></i>
                </button>

                <div
                    v-show="isMenuOpen(child)"
                    class="app-sidebar-nested-submenu"
                >
                  <template v-for="nestedChild in getVisibleChildren(child)" :key="nestedChild.key">
                    <button
                        v-if="isItemDisabled(nestedChild)"
                        class="app-sidebar-nested-link is-disabled"
                        type="button"
                        disabled
                    >
                      {{ nestedChild.label }}

                      <span class="app-sidebar-lock">
                        <i class="fa-solid fa-lock"></i>
                      </span>
                    </button>

                    <NuxtLink
                        v-else
                        :to="nestedChild.to"
                        class="app-sidebar-nested-link"
                        :class="{ 'is-active': isItemActive(nestedChild) }"
                        @click="closeMobileSidebar"
                    >
                      {{ nestedChild.label }}
                    </NuxtLink>
                  </template>
                </div>
              </template>
            </template>
          </div>
        </template>
      </template>
    </nav>

    <div class="app-sidebar-footer">
      <button
          class="app-sidebar-link app-sidebar-logout"
          type="button"
          @click="logout"
      >
        <span class="app-sidebar-link-icon">
          <i class="fa-solid fa-right-from-bracket"></i>
        </span>

        <span class="app-sidebar-link-label">Sair</span>
      </button>
    </div>

    <div
        v-if="sidebarUsesCollapsedLayout && activeFlyoutItem"
        class="app-sidebar-flyout"
        :style="{ top: `${flyoutTop}px` }"
        @mouseenter="clearCloseFlyoutTimeout"
        @mouseleave="scheduleCloseFlyout"
    >
      <strong class="app-sidebar-flyout-title">
        {{ activeFlyoutItem.label }}
      </strong>

      <template v-for="child in getVisibleChildren(activeFlyoutItem)" :key="child.key">
        <button
            v-if="!itemHasChildren(child) && isItemDisabled(child)"
            class="app-sidebar-flyout-link is-disabled"
            type="button"
            disabled
        >
          <i v-if="child.icon" :class="child.icon"></i>
          <span>{{ child.label }}</span>

          <span class="app-sidebar-lock">
            <i class="fa-solid fa-lock"></i>
          </span>
        </button>

        <NuxtLink
            v-else-if="!itemHasChildren(child)"
            :to="child.to"
            class="app-sidebar-flyout-link"
            :class="{ 'is-active': isItemActive(child) }"
            @click="closeFlyout"
        >
          <i v-if="child.icon" :class="child.icon"></i>
          <span>{{ child.label }}</span>
        </NuxtLink>

        <template v-else>
          <button
              class="app-sidebar-flyout-link app-sidebar-flyout-link-button"
              :class="{ 'is-active': isItemActive(child), 'is-disabled': isItemDisabled(child) }"
              type="button"
              :disabled="isItemDisabled(child)"
              @click="toggleMenu(child)"
          >
            <i v-if="child.icon" :class="child.icon"></i>
            <span>{{ child.label }}</span>

            <span v-if="isItemDisabled(child)" class="app-sidebar-lock">
              <i class="fa-solid fa-lock"></i>
            </span>

            <i
                v-else
                class="fa-solid fa-chevron-down app-sidebar-flyout-arrow"
                :class="{ 'rotate-180': isMenuOpen(child) }"
            ></i>
          </button>

          <div
              v-show="isMenuOpen(child)"
              class="app-sidebar-flyout-nested"
          >
            <template v-for="nestedChild in getVisibleChildren(child)" :key="nestedChild.key">
              <button
                  v-if="isItemDisabled(nestedChild)"
                  class="app-sidebar-flyout-nested-link is-disabled"
                  type="button"
                  disabled
              >
                {{ nestedChild.label }}

                <span class="app-sidebar-lock">
                  <i class="fa-solid fa-lock"></i>
                </span>
              </button>

              <NuxtLink
                  v-else
                  :to="nestedChild.to"
                  class="app-sidebar-flyout-nested-link"
                  :class="{ 'is-active': isItemActive(nestedChild) }"
                  @click="closeFlyout"
              >
                {{ nestedChild.label }}
              </NuxtLink>
            </template>
          </div>
        </template>
      </template>
    </div>

    <button
        class="app-sidebar-collapse"
        type="button"
        @click="toggleSidebarCollapsed"
    >
      <i :class="sidebarCollapsed ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-left'"></i>
    </button>
  </aside>
</template>
