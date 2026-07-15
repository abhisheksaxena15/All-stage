import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/admin/product/')({
  component: RouteComponent,
})

function RouteComponent() {
  return <div>Hello "/admin/product/"!</div>
}
