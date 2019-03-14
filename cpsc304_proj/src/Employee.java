import java.sql.ResultSet;
import java.sql.SQLException;

public class Employee {
	
	OracleConnect oracleConnect;
	String employeeID;
	
	public Employee(){	
		oracleConnect = new OracleConnect();
		employeeID = "123";
		
		//need function to get login employeeID;
	}
	
	public ResultSet findOrder(String id) throws SQLException{
		if (id.isEmpty()) {
			return oracleConnect.selectAll("order");
			}
		else return oracleConnect.getStatement().executeQuery("SELECT FROM order WHERE id = " + id );
	}
	
	public ResultSet findEmployee(){
		ResultSet result = null;
		if (employeeID.isEmpty()) {
			return result;
			} else
			try {
				 result = oracleConnect.getStatement().executeQuery("SELECT FROM employee WHERE id = " + employeeID );
			} catch (SQLException e) {
				e.printStackTrace();
				System.out.println("Cannot find employee.");
			}
		return result;
		
	}
	
	public void deleteOrder(String id){
		try {
			oracleConnect.getStatement().executeQuery("DELETE FROM order WHERE id = " + id);
		} catch (SQLException e) {
			e.printStackTrace();
			System.out.println("Cannot delete order.");
		}
	}
}
