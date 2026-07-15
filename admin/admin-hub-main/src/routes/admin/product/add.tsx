import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/admin/product/add')({
  component: RouteComponent,
})

function RouteComponent() {
  return <div>Hello "/admin/product/add"!</div>
}
