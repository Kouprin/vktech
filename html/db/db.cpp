#include "db.h"

orders_table_t order_table;

exec parse_query(const char* buf, size_t len) {
    try {
        std::stringstream ss;
        ss << buf;
        boost::property_tree::ptree pt;
        boost::property_tree::read_json(ss, pt);
        //ptree::const_iterator it = pt.find("query");
        std::string table = pt.get<std::string>("table");
        std::cerr << "table=" << table << std::endl;
        return SUCCESS;
    }
    catch (std::exception const& e)
    {
        std::cerr << e.what() << std::endl;
    }
    return FAIL;
}
